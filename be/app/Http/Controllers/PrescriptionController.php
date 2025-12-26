<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::with('patient', 'doctor', 'items')->latest()->paginate(15);
        return response()->json([
            "success" => true,
            "message" => "Prescription Details retrieved successfully.",
            "data" => $prescriptions
        ], 200);
    }

    public function create(Patient $patient)
    {
        $drugs = Drug::where('stock_quantity', '>', 0)->get();
        return view('prescriptions.create', compact('patient', 'drugs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'consultation_id' => 'required|exists:consultations,id',
            'notes' => 'nullable|string',
            'drugs' => 'required|array|min:1',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.quantity' => 'required|integer|min:1',
            'drugs.*.dosage_instructions' => 'required|string'
        ]);

        // Create prescription
        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'consultation_id' => $validated['consultation_id'],
            'doctor_id' => $request['doctor_id'],
            'notes' => $validated['notes'] ?? null
        ]);

        // Add prescription items
        foreach ($validated['drugs'] as $drugData) {
            $drug = Drug::find($drugData['drug_id']);

            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'drug_id' => $drugData['drug_id'],
                'quantity_prescribed' => $drugData['quantity'],
                'dosage_instructions' => $drugData['dosage_instructions'],
                'unit_price' => $drug->selling_price
            ]);
        }

        // Update patient status to completed
        \App\Models\Patient::where('id', $validated['patient_id'])
            ->update(['status' => 'completed']);

        return response()->json([
            "success" => true,
            "message" => "Prescription created successfully!",
            "data" => $prescription
        ], 201);
    }

    public function show($id)
    {
        $prescription = Prescription::findOrFail($id);
        $prescription->load('patient', 'doctor', 'items.drug', 'consultation');
        return response()->json([
            'success' => true,
            'message' => 'Prescription Details retrieved successfully.',
            'data'    => $prescription,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'drugs' => 'required|array|min:1',
            'drugs.*.id' => 'nullable|exists:prescription_items,id',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.quantity_prescribed' => 'required|integer|min:1',
            'drugs.*.dosage_instructions' => 'required|string',
            'original_drugs' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            $prescription = Prescription::findOrFail($id);
            
            // Update prescription notes
            $prescription->notes = $validated['notes'] ?? null;
            $prescription->save();

            // Get existing prescription items with dispensed status
            $existingItems = PrescriptionItem::where('prescription_id', $prescription->id)->get();
            $existingItemsMap = $existingItems->keyBy('id');
            
            // Identify dispensed items that must be preserved
            $dispensedItems = $existingItems->filter(function($item) {
                return $item->quantity_dispensed > 0;
            });

            // Track which items to keep (dispensed ones) and what actions were blocked
            $itemsToKeep = [];
            $blockedActions = [];
            $updatedCount = 0;
            $addedCount = 0;
            
            // Check for attempts to remove or modify dispensed drugs
            foreach ($dispensedItems as $dispensedItem) {
                $itemsToKeep[] = $dispensedItem->id;
                
                // Check if user tried to remove this dispensed drug
                $stillInUpdate = collect($validated['drugs'])->contains(function($drug) use ($dispensedItem) {
                    return isset($drug['id']) && $drug['id'] == $dispensedItem->id;
                });
                
                if (!$stillInUpdate) {
                    // User tried to remove a dispensed drug
                    $blockedActions[] = [
                        'action' => 'delete',
                        'drug_name' => $dispensedItem->drug ? $dispensedItem->drug->name : 'Unknown',
                        'quantity_dispensed' => $dispensedItem->quantity_dispensed,
                        'message' => 'Cannot delete - already dispensed'
                    ];
                } else {
                    // Check if user tried to modify the dispensed drug
                    $updateAttempt = collect($validated['drugs'])->firstWhere('id', $dispensedItem->id);
                    if ($updateAttempt) {
                        $hasChanges = ($updateAttempt['drug_id'] != $dispensedItem->drug_id) ||
                                     ($updateAttempt['quantity_prescribed'] != $dispensedItem->quantity_prescribed) ||
                                     ($updateAttempt['dosage_instructions'] != $dispensedItem->dosage_instructions);
                        
                        if ($hasChanges) {
                            $blockedActions[] = [
                                'action' => 'modify',
                                'drug_name' => $dispensedItem->drug ? $dispensedItem->drug->name : 'Unknown',
                                'quantity_dispensed' => $dispensedItem->quantity_dispensed,
                                'message' => 'Cannot modify - already dispensed'
                            ];
                        }
                    }
                }
            }

            // Process new/updated drugs
            $originalDrugsMap = collect($validated['original_drugs'])->keyBy('id');
            $newDrugIds = collect($validated['drugs'])->pluck('drug_id')->toArray();
            
            // Return stock for removed non-dispensed drugs
            foreach ($validated['original_drugs'] as $originalDrug) {
                // Check if this drug is in the new list
                $stillInPrescription = collect($validated['drugs'])->contains(function($newDrug) use ($originalDrug) {
                    return isset($newDrug['id']) && $newDrug['id'] == $originalDrug['id'];
                });
                
                if (!$stillInPrescription) {
                    // Drug was removed - check if it was dispensed
                    $originalItem = $existingItemsMap->get($originalDrug['id']);
                    
                    if ($originalItem && $originalItem->quantity_dispensed > 0) {
                        // This drug was dispensed, keep it (already tracked in blockedActions)
                        continue;
                    }
                    
                    // Drug was not dispensed, return stock
                    $drug = Drug::find($originalDrug['drug_id']);
                    if ($drug) {
                        $drug->stock_quantity += $originalDrug['quantity_prescribed'];
                        $drug->save();
                    }
                    
                    // Delete this non-dispensed item
                    if ($originalItem) {
                        $originalItem->delete();
                    }
                }
            }

            // Process each drug in the update
            foreach ($validated['drugs'] as $drugData) {
                $drug = Drug::find($drugData['drug_id']);
                
                // Check if this is an existing item
                if (isset($drugData['id']) && $drugData['id']) {
                    $existingItem = $existingItemsMap->get($drugData['id']);
                    
                    // If item was dispensed, skip updating it
                    if ($existingItem && $existingItem->quantity_dispensed > 0) {
                        continue; // Keep the dispensed item as-is (already tracked in blockedActions)
                    }
                    
                    // Item exists but not dispensed - update it
                    if ($existingItem) {
                        $originalDrug = $originalDrugsMap->get($drugData['id']);
                        
                        if ($originalDrug) {
                            $quantityDifference = $drugData['quantity_prescribed'] - $originalDrug['quantity_prescribed'];
                            
                            if ($quantityDifference > 0) {
                                // Increased quantity - deduct additional from stock
                                if ($drug->stock_quantity < $quantityDifference) {
                                    throw new \Exception("Insufficient stock for {$drug->name}. Available: {$drug->stock_quantity}");
                                }
                                $drug->stock_quantity -= $quantityDifference;
                            } else if ($quantityDifference < 0) {
                                // Decreased quantity - return difference to stock
                                $drug->stock_quantity += abs($quantityDifference);
                            }
                            
                            $drug->save();
                        }
                        
                        // Update the existing item
                        $existingItem->drug_id = $drugData['drug_id'];
                        $existingItem->quantity_prescribed = $drugData['quantity_prescribed'];
                        $existingItem->dosage_instructions = $drugData['dosage_instructions'];
                        $existingItem->unit_price = $drug->selling_price;
                        $existingItem->save();
                        
                        $updatedCount++;
                    }
                } else {
                    // New drug being added to prescription
                    if ($drug->stock_quantity < $drugData['quantity_prescribed']) {
                        throw new \Exception("Insufficient stock for {$drug->name}. Available: {$drug->stock_quantity}");
                    }
                    
                    $drug->stock_quantity -= $drugData['quantity_prescribed'];
                    $drug->save();
                    
                    // Create new prescription item
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'drug_id' => $drugData['drug_id'],
                        'quantity_prescribed' => $drugData['quantity_prescribed'],
                        'quantity_dispensed' => 0,
                        'dosage_instructions' => $drugData['dosage_instructions'],
                        'unit_price' => $drug->selling_price
                    ]);
                    
                    $addedCount++;
                }
            }

            DB::commit();

            // Reload prescription with relationships
            $prescription->load('patient', 'doctor', 'items.drug', 'consultation');

            // Build appropriate message based on what happened
            $message = '';
            $messageType = 'success';
            
            if (count($blockedActions) > 0) {
                $messageType = 'warning';
                
                $deletedDrugs = array_filter($blockedActions, function($a) {
                    return $a['action'] === 'delete';
                });
                $modifiedDrugs = array_filter($blockedActions, function($a) {
                    return $a['action'] === 'modify';
                });
                
                $messageParts = [];
                
                if (count($deletedDrugs) > 0) {
                    $drugNames = implode(', ', array_column($deletedDrugs, 'drug_name'));
                    $messageParts[] = "Cannot delete dispensed medication(s): {$drugNames}";
                }
                
                if (count($modifiedDrugs) > 0) {
                    $drugNames = implode(', ', array_column($modifiedDrugs, 'drug_name'));
                    $messageParts[] = "Cannot modify dispensed medication(s): {$drugNames}";
                }
                
                $message = implode('. ', $messageParts) . '. ';
                
                // Add success part if something was actually updated
                if ($updatedCount > 0 || $addedCount > 0) {
                    $successParts = [];
                    if ($addedCount > 0) {
                        $successParts[] = "{$addedCount} new medication(s) added";
                    }
                    if ($updatedCount > 0) {
                        $successParts[] = "{$updatedCount} medication(s) updated";
                    }
                    $message .= 'However, ' . implode(' and ', $successParts) . ' successfully.';
                } else {
                    $message .= 'No changes were made to the prescription.';
                }
            } else {
                // No blocked actions
                if ($addedCount > 0 && $updatedCount > 0) {
                    $message = "Prescription updated successfully. {$addedCount} medication(s) added and {$updatedCount} updated.";
                } else if ($addedCount > 0) {
                    $message = "Prescription updated successfully. {$addedCount} new medication(s) added.";
                } else if ($updatedCount > 0) {
                    $message = "Prescription updated successfully. {$updatedCount} medication(s) updated.";
                } else {
                    $message = "Prescription notes updated successfully.";
                }
            }

            return response()->json([
                'success' => true,
                'message_type' => $messageType,
                'message' => $message,
                'data' => $prescription,
                'blocked_actions' => $blockedActions,
                'summary' => [
                    'items_added' => $addedCount,
                    'items_updated' => $updatedCount,
                    'items_blocked' => count($blockedActions)
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prescription: ' . $e->getMessage()
            ], 500);
        }
    }
}
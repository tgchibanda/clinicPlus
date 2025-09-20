<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\Drug;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::with('patient', 'doctor', 'items')->latest()->paginate(15);
        return response()->json([
            "success" => true,
            "message" => "Prescrition Details retrieved successfully.",
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
            'notes' => 'nullable|string',
            'drugs' => 'required|array|min:1',
            'drugs.*.drug_id' => 'required|exists:drugs,id',
            'drugs.*.quantity' => 'required|integer|min:1',
            'drugs.*.dosage_instructions' => 'required|string'
        ]);

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $request['doctor_id'],
            'notes' => $validated['notes']
        ]);

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

        return response()->json([
            "success" => true,
            "message" => "Prescription created successfully!",
            "data" => $prescription
        ], 201);
    }

    public function xshow(Prescription $prescription)
    {
        $prescription->load('patient', 'doctor', 'items.drug');
        return view('prescriptions.show', compact('prescription'));
    }

    public function show($id)
    {
        $prescription = Prescription::findOrFail($id);
        // load relationships
        $prescription->load('patient', 'doctor', 'items.drug');
        return response()->json([
            'success' => true,
            'message' => 'Prescription Details retrieved successfully.',
            'data'    => $prescription,
        ]);
    }
}

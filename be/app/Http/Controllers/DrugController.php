<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugRestocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DrugController extends Controller
{
    public function index()
    {        
        $drugs = Drug::latest()->paginate(15);
        $lowStockDrugs = Drug::where('stock_quantity', '<=', \DB::raw('minimum_stock_level'))->count();
        return response()->json([
            "success" => true,
            "message" => "Drugs Details retrieved successfully.",
            "data" => [
                "drugs" => $drugs,
                "low_stock_count" => $lowStockDrugs
            ]
        ], 200);
    }

    public function create()
    {
        return view('drugs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        Drug::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Drug Details added successfully.',
        ]);
    }

    public function show(Drug $drug)
    {
        return view('drugs.show', compact('drug'));
    }

    public function drugDetails($id)
    {
        $drug = Drug::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Drug Details retrieved successfully.',
            'data'    => $drug,
        ]);
    }

    public function edit(Drug $drug)
    {
        return view('drugs.edit', compact('drug'));
    }

    public function update(Request $request, $id)
    {
        $drug = Drug::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'batch_number' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        $drug->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Drug updated successfully!',
            'data' => $drug
        ]);
    }

    public function addStock(Request $request, $id)
    {
        $drug = Drug::findOrFail($id);
        
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $previousQuantity = $drug->stock_quantity;
        $newQuantity = $previousQuantity + $validated['quantity'];

        // Update drug stock
        $drug->update(['stock_quantity' => $newQuantity]);

        // Create audit record
        DrugRestocking::create([
            'drug_id' => $drug->id,
            'user_id' => Auth::id() ?? $request->user_id,
            'quantity_added' => $validated['quantity'],
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $newQuantity,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully!',
            'data' => $drug->fresh()
        ]);
    }

    public function destroy($id)
    {
        $drug = Drug::findOrFail($id);
        
        // Check if drug exists in prescription_items
        $prescriptionCount = \DB::table('prescription_items')
            ->where('drug_id', $id)
            ->count();
        
        if ($prescriptionCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This drug cannot be deleted as it has been prescribed to patients. Found in ' . $prescriptionCount . ' prescription(s).',
            ], 422);
        }
        
        // Safe to delete
        $drug->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Drug deleted successfully.',
        ]);
    }

    public function restockingHistory($id)
    {
        $drug = Drug::findOrFail($id);
        $history = DrugRestocking::where('drug_id', $id)
            ->with('user')
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Restocking history retrieved successfully.',
            'data' => [
                'drug' => $drug,
                'history' => $history
            ]
        ]);
    }
}
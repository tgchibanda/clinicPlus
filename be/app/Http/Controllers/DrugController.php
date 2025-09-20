<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use Illuminate\Http\Request;

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
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        Drug::create($validated);

        return redirect()->route('drugs.index')
            ->with('success', 'Drug added successfully!');
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

    public function update(Request $request, Drug $drug)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock_level' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        $drug->update($validated);

        return redirect()->route('drugs.index')
            ->with('success', 'Drug updated successfully!');
    }

    public function updateStock(Request $request, Drug $drug)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract'
        ]);

        if ($request->type === 'add') {
            $drug->increment('stock_quantity', $request->quantity);
        } else {
            $drug->decrement('stock_quantity', $request->quantity);
        }

        return back()->with('success', 'Stock updated successfully!');
    }
}
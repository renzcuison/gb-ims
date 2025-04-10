<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class STOCKSController extends Controller
{
    public function index()
    {
        $stocks = Stock::with('category', 'supplier', 'skus')->get();

        return $stocks->isNotEmpty()
            ? response()->json(['status' => 200, 'stocks' => $stocks], 200)
            : response()->json(['status' => 404, 'message' => 'No records found.'], 404);
    }

    public function store(Request $request)
    {   
        try{
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_of_measure' => 'required|string|max:50',
            'physical_count' => 'required|integer|min:0',
            'on_hand' => 'required|integer|min:0',
            'sold' => 'required|integer|min:0',
            'date' => 'nullable|date',
            'price_per_unit' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => 422, 'errors' => $validator->messages()], 422);
        }
    
        $existingStock = Stock::where('item_name', $request->item_name)
            ->where('unit_of_measure', $request->unit_of_measure)
            ->first();
    
        if ($existingStock) {
            return response()->json([
                'status' => 409, 
                'message' => 'A stock with the same item name and unit of measure already exists.',
            ], 409);
        }
    
        $currentDate = Carbon::now()->format('my');
        $lastItem = Stock::where('id', 'like', "{$currentDate}%")
            ->orderBy('id', 'desc')
            ->first();
    
        $newNumber = 1;
        if ($lastItem) {
            $lastNumber = (int) substr($lastItem->id, -3);
            $newNumber = $lastNumber + 1;
        }
    
        $newID = $currentDate . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    
        $newStock = Stock::create([
            'id' => $newID,
            'item_name' => $request->item_name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'unit_of_measure' => $request->unit_of_measure,
            'physical_count' => $request->physical_count,
            'on_hand' => $request->on_hand,
            'sold' => $request->sold,
            'date' => $request->date,
            'price_per_unit' => $request->price_per_unit,
            'buying_price' => $request->buying_price
        ]);
    
        return response()->json([
            'status' => 200,
            'message' => 'New stock added successfully.',
            'stock' => $newStock,
        ], 200);
    }catch (\Exception $e) {
        \Log::error("Error saving stock: " . $e->getMessage());
        return response()->json(['status' => 500, 'message' => 'Internal server error'], 500);
    }
    }
       
    public function show($id)
    {
        $stock = Stock::with('category', 'supplier', 'skus')->find($id);

        return $stock
            ? response()->json(['status' => 200, 'stock' => $stock], 200)
            : response()->json(['status' => 404, 'message' => 'Stock not found.'], 404);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_of_measure' => 'required|string|max:50',
            'physical_count' => 'required|integer|min:0',
            'on_hand' => 'required|integer|min:0',
            'sold' => 'required|integer|min:0',
            'date' => 'nullable|date',
            'price_per_unit' => 'required|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'skus' => 'nullable|array',
            'skus.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'errors' => $validator->messages()], 422);
        }

        $stock = Stock::find($id);

        if ($stock) {
            $stock->update([
                'item_name' => $request->item_name,
                'description' => $request->description ?? $stock->description,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'unit_of_measure' => $request->unit_of_measure,
                'physical_count' => $request->physical_count,
                'on_hand' => $request->on_hand,
                'sold' => $request->sold,
                'date' => $request->date,
                'price_per_unit' => $request->price_per_unit,
                'buying_price' => $request->buying_price
            ]);

            if ($request->sku) {
                $existingSku = $stock->skus()->where('sku', $request->sku)->first();
                if (!$existingSku) {
                    Sku::create([
                        'sku' => $request->sku,
                        'stock_id' => $stock->id,
                    ]);
                }
            }

            if ($request->has('skus') && is_array($request->skus)) {
                foreach ($request->skus as $sku) {
                    $existingSku = $stock->skus()->where('sku', $sku)->first();
                    if (!$existingSku) {
                        Sku::create([
                            'sku' => $sku,
                            'stock_id' => $stock->id,
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Stock updated successfully.',
                'stock' => $stock->load('skus'),
            ], 200);
        }

        return response()->json(['status' => 404, 'message' => 'Stock not found.'], 404);
    }

    public function destroy($id)
    {
        $stock = Stock::find($id);

        if ($stock) {
            $stock->skus()->delete();
            $stock->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Stock deleted successfully.',
            ], 200);
        }

        return response()->json(['status' => 404, 'message' => 'Stock not found.'], 404);
    }

    public function addSku(Request $request, $stockId)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 422, 'errors' => $validator->messages()], 422);
        }

        $stock = Stock::find($stockId);

        if ($stock) {
            $sku = new Sku();
            $sku->sku = $request->sku;
            $sku->stock_id = $stock->id;
            $sku->save();

            return response()->json([
                'status' => 200,
                'message' => 'SKU added successfully.',
                'sku' => $sku,
            ], 200);
        }

        return response()->json(['status' => 404, 'message' => 'Stock not found.'], 404);
    }
    public function removeSku($stockId, $skuId)
    {
        $stock = Stock::find($stockId);
    
        if ($stock) {
            $sku = $stock->skus()->where('sku', $skuId)->first();
    
            if ($sku) {
                $sku->delete();
                return response()->json([
                    'status' => 200,
                    'message' => 'SKU removed successfully.',
                ], 200);
            }
    
            return response()->json(['status' => 404, 'message' => 'SKU not found.'], 404);
        }
    
        return response()->json(['status' => 404, 'message' => 'Stock not found.'], 404);
    }    
}

<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class STOCKSController extends Controller
{
    public function index()
    {
        $stocks = Stock::with('item')->get();
        if ($stocks->count() > 0) {
            return response()->json([
                'status' => 200,
                'stocks' => $stocks
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'No records found.'
            ], 404);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'unit_of_measure' => 'required|string',
            'description' => 'nullable|string',
            'time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $request->all();

        $existingStock = Stock::where('item_id', $payload['item_id'])
            ->where('unit_of_measure', $payload['unit_of_measure'])
            ->first();

        if ($existingStock) {
            $existingStock->quantity += $payload['quantity'];
            $existingStock->save();

            return response()->json([
                'status' => 200,
                'message' => 'Stock quantity updated successfully.',
                'stock' => $existingStock
            ], 200);
        }

        $newStock = Stock::create([
            'item_id' => $request->item_id,
            'item_name' => $request->item_name,
            'quantity' => $request->quantity,
            'supplier_id' => $request->supplier_id,
            'unit_of_measure' => $request->unit_of_measure,
            'description' => $request->description,
            'time' => $request->time ?? now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'New stock added successfully.',
            'stock' => $newStock
        ], 200);
    }

    public function show($id)
    {
        $stock = Stock::find($id);
        if ($stock) {
            return response()->json([
                'status' => 200,
                'stock' => $stock
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Stock not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'supplier_id' => 'required|exists:suppliers,id',
            'unit_of_measure' => 'required|string',
            'description' => 'nullable|string',
            'time' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $stock = Stock::find($id);

        if ($stock) {
            $conflict = Stock::where('item_id', $request->item_id)
                ->where('unit_of_measure', $request->unit_of_measure)
                ->where('id', '!=', $id)
                ->first();

            if ($conflict) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Conflict: Same item and unit of measure already exists.'
                ], 409);
            }

            $stock->update($request->all());

            return response()->json([
                'status' => 200,
                'message' => 'Stock updated successfully.',
                'stock' => $stock
            ], 200);
        }

        return response()->json([
            'status' => 404,
            'message' => 'Stock not found.'
        ], 404);
    }


    public function destroy($id)
    {
        $stock = Stock::find($id);
        if ($stock) {
            $stock->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Stock deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Stock not found.'
            ], 404);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ITEMSController extends Controller
{
    public function index()
    {
        $items = Item::with('category', 'supplier')->get();
        if ($items->count() > 0) {
            return response()->json([
                'status' => 200,
                'items' => $items
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $item = Item::create([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'price_per_unit' => $request->price_per_unit,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Item Added successfully.',
            'item' => $item
        ], 200);
    }

    public function show($id)
    {
        $item = Item::with('category', 'supplier')->find($id);
        if ($item) {
            return response()->json([
                'status' => 200,
                'item' => $item
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Item not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $item = Item::find($id);
        if ($item) {
            $item->update([
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'supplier_id' => $request->supplier_id,
                'price_per_unit' => $request->price_per_unit,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Item updated successfully.',
                'item' => $item
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Item not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $item = Item::find($id);
        if ($item) {
            $item->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Item deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Item not found.'
            ], 404);
        }
    }
}
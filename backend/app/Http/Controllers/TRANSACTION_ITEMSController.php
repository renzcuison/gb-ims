<?php

namespace App\Http\Controllers;

use App\Models\Transaction_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TRANSACTION_ITEMSController extends Controller
{
    public function index()
    {
        $transactionItems = Transaction_Item::with(['inventoryTransaction', 'item'])->get();
        if ($transactionItems->count() > 0) {
            return response()->json([
                'status' => 200,
                'transaction_items' => $transactionItems
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
            'inventory_transactions_id' => 'required|exists:inventory_transactions,id',  
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $transactionItem = Transaction_Item::create([
            'inventory_transactions_id' => $request->inventory_transactions_id,  
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Transaction item created successfully.',
            'transaction_item' => $transactionItem
        ], 200);
    }

    public function show($id)
    {
        $transactionItem = Transaction_Item::with(['inventoryTransaction', 'item'])->find($id);
        if ($transactionItem) {
            return response()->json([
                'status' => 200,
                'transaction_item' => $transactionItem
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction item not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        $transactionItem = Transaction_Item::find($id);
        if ($transactionItem) {
            $transactionItem->update([
                'quantity' => $request->quantity,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Transaction item updated successfully.',
                'transaction_item' => $transactionItem
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction item not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $transactionItem = Transaction_Item::find($id);
        if ($transactionItem) {
            $transactionItem->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Transaction item deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction item not found.'
            ], 404);
        }
    }

    public function attachItems(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }

        foreach ($request->items as $item) {
            Transaction_Item::create([
                'inventory_transactions_id' => $id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Items added to transaction successfully.'
        ], 200);
    }
}

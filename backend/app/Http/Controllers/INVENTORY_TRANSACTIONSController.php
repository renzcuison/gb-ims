<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inventory_Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class INVENTORY_TRANSACTIONSController extends Controller
{
    public function index()
    {
        $transactions = Inventory_Transaction::with('items')->get();
        if ($transactions->count() > 0) {
            return response()->json([
                'status' => 200,
                'transactions' => $transactions
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
        \Log::info('Incoming request data: ', $request->all());

        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:inbound,outbound',
            'transaction_date' => 'required|date',
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

        $transaction = Inventory_Transaction::create([
            'transaction_type' => $request->transaction_type,
            'transaction_date' => $request->transaction_date,
        ]);

        foreach ($request->items as $item) {
            $transaction->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Transaction created successfully.',
            'transaction' => $transaction
        ], 200);
    }

    public function show($id)
    {
        $transaction = Inventory_Transaction::with('items')->find($id);
        if ($transaction) {
            return response()->json([
                'status' => 200,
                'transaction' => $transaction
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:inbound,outbound',
            'transaction_date' => 'required|date',
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

        $transaction = Inventory_Transaction::find($id);
        if ($transaction) {
            $transaction->update([
                'transaction_type' => $request->transaction_type,
                'transaction_date' => $request->transaction_date,
            ]);

            $transaction->items()->detach();
            foreach ($request->items as $item) {
                $transaction->items()->attach($item['item_id'], ['quantity' => $item['quantity']]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Transaction updated successfully.',
                'transaction' => $transaction
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        $transaction = Inventory_Transaction::find($id);
        if ($transaction) {
            $transaction->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Transaction deleted.'
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Transaction not found.'
            ], 404);
        }
    }
}

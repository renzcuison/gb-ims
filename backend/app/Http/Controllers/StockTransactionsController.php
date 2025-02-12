<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransaction;
use App\Models\StockTransactionDetails;
use App\Models\Stock;
use App\Models\Sku;
use Illuminate\Support\Facades\Auth;

class StockTransactionsController extends Controller
{
    public function createTransaction(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:in,out',
            'reason' => 'required|string',
            'details' => 'required|array',
            'details.*.stock_id' => 'required|exists:stocks,id',
            'details.*.sku' => 'nullable|string',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        $transaction = StockTransaction::create([
            'transaction_type' => $validated['transaction_type'],
            'reason' => $validated['reason'],
            'created_by' => Auth::id(),
        ]);

        foreach ($validated['details'] as $detail) {
            $stock = Stock::findOrFail($detail['stock_id']);
            $sku = $detail['sku'] ? Sku::where('sku', $detail['sku'])->where('stock_id', $stock->id)->first() : null;

            if ($validated['transaction_type'] === 'out' && $stock->on_hand < $detail['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for {$stock->item_name}",
                ], 400);
            }

            $transaction->details()->create([
                'stock_id' => $detail['stock_id'],
                'sku' => $sku ? $sku->sku : null,
                'quantity' => $detail['quantity'],
                'price_per_unit' => $stock->price_per_unit,
            ]);

            if ($validated['transaction_type'] === 'in') {
                $stock->increment('on_hand', $detail['quantity']);
            } else {
                $stock->decrement('on_hand', $detail['quantity']);
            }
        }

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction->load('details'),
        ]);
    }
    public function fetchTransactionsByStock(Request $request)
    {
        $request->validate(['stock_id' => 'required|exists:stocks,id']);

        $transactions = StockTransaction::with(['details', 'user'])
            ->whereHas('details', function ($query) use ($request) {
                $query->where('stock_id', $request->stock_id);
            })
            ->get();

        return response()->json($transactions);
    }
    public function getReceipt($id)
    {
        $transaction = StockTransaction::with(['details.stock', 'user'])->findOrFail($id);

        return response()->json([
            'transaction' => $transaction,
        ]);
    }
}

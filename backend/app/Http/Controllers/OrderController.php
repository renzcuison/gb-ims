<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('stock')->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_order_id' => 'required|exists:customer_orders,id',
            'stock_id' => 'required|string|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $stock = Stock::where('id', $validated['stock_id'])->firstOrFail();
            $validated['price_per_unit'] = $stock->price_per_unit;

            // ✅ Check if item already exists in the order
            $existingOrder = Order::where('customer_order_id', $validated['customer_order_id'])
                ->where('stock_id', $validated['stock_id'])
                ->first();

            if ($existingOrder) {
                // ✅ If item exists, update the quantity
                $existingOrder->quantity += $validated['quantity'];
                $existingOrder->save();
                $order = $existingOrder;
            } else {
                // ✅ If it's a new item, create a new order entry
                $order = Order::create($validated);
            }

            DB::commit();
            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update order.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with('stock')->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'customer_order_id' => 'required|exists:customer_orders,id',
            'stock_id' => 'required|string|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $stock = Stock::where('id', $validated['stock_id'])->firstOrFail();
        $validated['price_per_unit'] = $stock->price_per_unit;

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}

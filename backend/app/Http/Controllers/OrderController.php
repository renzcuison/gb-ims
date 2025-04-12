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
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $stock = Stock::findOrFail($validated['stock_id']);
            $validated['price_per_unit'] = $stock->price_per_unit;

            $order = Order::updateOrCreate(
                [
                    'customer_order_id' => $validated['customer_order_id'],
                    'stock_id' => $validated['stock_id'],
                ],
                ['quantity' => DB::raw("quantity + {$validated['quantity']}"), 'price_per_unit' => $validated['price_per_unit']]
            );

            DB::commit();
            return response()->json(['message' => 'Item added to cart successfully', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update order.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $order = Order::with('stock')->find($id);
        return $order ? response()->json($order) : response()->json(['error' => 'Order not found.'], 404);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'customer_order_id' => 'required|exists:customer_orders,id',
            'stock_id' => 'required|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['price_per_unit'] = Stock::findOrFail($validated['stock_id'])->price_per_unit;

        $order->update($validated);

        return response()->json(['message' => 'Order updated successfully', 'order' => $order]);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}

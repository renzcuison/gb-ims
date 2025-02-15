<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;

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
            'stock_id' => 'required|integer|exists:stocks,id', 
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Stock::findOrFail($validated['stock_id']);
        $validated['price_per_unit'] = $item->price_per_unit;

        $order = Order::create($validated);

        return response()->json($order, 201);
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
            'stock_id' => 'required|integer|exists:stocks,id', 
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Stock::findOrFail($validated['stock_id']);
        $validated['price_per_unit'] = $item->price_per_unit;

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(null, 204);
    }
}

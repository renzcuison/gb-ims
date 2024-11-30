<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Item;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('item')->get(); 
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|string|exists:items,id', 
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $validated['item_price_per_unit'] = $item->price_per_unit;

        $order = Order::create($validated);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = Order::with('item')->findOrFail($id); 
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'item_id' => 'required|string|exists:items,id', 
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $validated['item_price_per_unit'] = $item->price_per_unit;

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

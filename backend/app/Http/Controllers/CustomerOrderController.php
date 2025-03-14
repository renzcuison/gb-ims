<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'shipping_address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
            'payment_method' => 'required|string',
            'total_price' => 'required|numeric',
        ]);

        $order = CustomerOrder::create(array_merge($validated, [
            'order_code' => uniqid('ORD-'),
        ]));

        return response()->json($order, 201);
    }

    public function index() {
        return response()->json(CustomerOrder::all());
    }
    
    public function destroy($id) {
        $order = CustomerOrder::find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        $order->delete();
    
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
    
}

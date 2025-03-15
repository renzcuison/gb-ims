<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function index()
    {
        return response()->json(CustomerOrder::with('orders.stock')->get());
    }

    public function show($id)
    {
        $customerOrder = CustomerOrder::find($id);

        if (!$customerOrder) {
            return response()->json(['message' => 'Customer order not found.'], 404);
        }

        return response()->json($customerOrder);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'shipping_address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
            'payment_method' => 'required|string',
            'total_price' => 'required|numeric',
            'stocks' => 'required|array',
            'stocks.*.stock_id' => 'required|exists:stocks,id',
            'stocks.*.quantity' => 'required|integer|min:1',
            'stocks.*.price_per_unit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create the customer order
            $order = CustomerOrder::create([
                'order_code' => uniqid('ORD-'),
                'customer_name' => $validated['customer_name'],
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'phone' => $validated['phone'],
                'payment_method' => $validated['payment_method'],
                'total_price' => $validated['total_price'],
            ]);

            // Process each ordered stock
            foreach ($validated['stocks'] as $stockData) {
                $stock = Stock::find($stockData['stock_id']);

                // Check if enough stock is available
                if ($stock->on_hand < $stockData['quantity']) {
                    return response()->json([
                        'error' => "Not enough stock available for item: {$stock->item_name}"
                    ], 400);
                }

                // Deduct stock quantity
                $stock->on_hand -= $stockData['quantity'];
                $stock->sold += $stockData['quantity']; // Track sold quantity
                $stock->save();

                // Store order details
                Order::create([
                    'customer_order_id' => $order->id,
                    'stock_id' => $stockData['stock_id'],
                    'quantity' => $stockData['quantity'],
                    'price_per_unit' => $stockData['price_per_unit'],
                ]);
            }

            DB::commit();
            return response()->json($order->load('orders.stock'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Order could not be placed.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $order = CustomerOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::beginTransaction();

        try {
            // Restore stock quantities
            foreach ($order->orders as $orderItem) {
                $stock = $orderItem->stock;
                $stock->on_hand += $orderItem->quantity;
                $stock->sold -= $orderItem->quantity;
                $stock->save();
            }

            // Delete order details first
            $order->orders()->delete();
            
            // Delete the main order
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Order canceled successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to cancel order.', 'message' => $e->getMessage()], 500);
        }
    }

}

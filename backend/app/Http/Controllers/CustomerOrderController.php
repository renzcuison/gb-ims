<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    if ($user->role === 'admin') {
        
        $orders = CustomerOrder::with('orders.stock')->get();
    } else {
        
        $orders = CustomerOrder::with('orders.stock')
            ->where('user_id', $user->id)
            ->get();
    }

    return response()->json($orders);
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
            'status' => 'nullable|string|in:Pending,Approved,Cancelled,Processing,Shipped,Delivered,Refunded,On Hold',
        ]);

        DB::beginTransaction();

        try {
            // Get authenticated user
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            // Create the customer order with user_id included
            $order = CustomerOrder::create([
                'order_code' => uniqid('ORD-'),
                'customer_name' => $validated['customer_name'],
                'shipping_address' => $validated['shipping_address'],
                'city' => $validated['city'],
                'postal_code' => $validated['postal_code'],
                'phone' => $validated['phone'],
                'payment_method' => $validated['payment_method'],
                'total_price' => $validated['total_price'],
                'status' => $validated['status'] ?? 'Pending',
                'user_id' => $user->id,  // **Important: assign user_id here**
            ]);

            // Process each ordered stock
            foreach ($validated['stocks'] as $stockData) {
                $stock = Stock::find($stockData['stock_id']);

                if ($stock->on_hand < $stockData['quantity']) {
                    return response()->json([
                        'error' => "Not enough stock available for item: {$stock->item_name}"
                    ], 400);
                }

                $stock->on_hand -= $stockData['quantity'];
                $stock->sold += $stockData['quantity'];
                $stock->save();

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

    public function update(Request $request, $id)
    {
        $order = CustomerOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:Pending,Approved,Cancelled,Processing,Shipped,Delivered,Refunded,On Hold',
        ]);

        $order->status = $validated['status'];
        $order->save();

        return response()->json($order, 200);
    }

    public function destroy($id)
    {
        $order = CustomerOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($order->orders as $orderItem) {
                $stock = $orderItem->stock;
                $stock->on_hand += $orderItem->quantity;
                $stock->sold -= $orderItem->quantity;
                $stock->save();
            }

            $order->orders()->delete();
            $order->delete();

            DB::commit();
            return response()->json(['message' => 'Order canceled successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to cancel order.', 'message' => $e->getMessage()], 500);
        }
    }
}


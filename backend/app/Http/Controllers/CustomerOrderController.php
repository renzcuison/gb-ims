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

        $orders = $user->role === 'admin'
            ? CustomerOrder::with('orders.stock')->get()
            : CustomerOrder::with('orders.stock')->where('user_id', $user->id)->get();

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
        $baseRules = [
            'customer_name' => 'required|string',
            'phone' => 'required|string',
            'payment_method' => 'required|string|in:cod,gcash',
            'total_price' => 'required|numeric',
            'stocks' => 'required|array',
            'stocks.*.stock_id' => 'required|exists:stocks,id',
            'stocks.*.quantity' => 'required|integer|min:1',
            'stocks.*.price_per_unit' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:Pending,Approved,Cancelled,Processing,Shipped,Delivered,Refunded,On Hold',
        ];

        // Conditionally required fields
        if ($request->payment_method === 'gcash') {
            $baseRules['shipping_address'] = 'required|string'; // used as reference number
        } else {
            $baseRules['shipping_address'] = 'nullable|string';
        }

        // City and postal code are no longer used but still exist in DB
        $baseRules['city'] = 'nullable|string';
        $baseRules['postal_code'] = 'nullable|string';

        $validated = $request->validate($baseRules);

        // Set empty values if not provided
        $validated['shipping_address'] = $validated['shipping_address'] ?? '';
        $validated['city'] = $validated['city'] ?? '';
        $validated['postal_code'] = $validated['postal_code'] ?? '';

        DB::beginTransaction();

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

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
                'user_id' => $user->id,
            ]);

            foreach ($validated['stocks'] as $stockData) {
                $stock = Stock::find($stockData['stock_id']);

                if ($stock->on_hand < $stockData['quantity']) {
                    DB::rollBack();
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

    public function initialize(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $customerOrder = CustomerOrder::where('user_id', $user->id)
            ->where('status', 'Pending') 
            ->first();

        if (!$customerOrder) {
            $customerOrder = CustomerOrder::create([
                'user_id' => $user->id,
                'order_code' => uniqid('CART-'), 
                'customer_name' => $user->name, 
                'phone' => $user->phone_number ?? '',
                'payment_method' => 'N/A', 
                'total_price' => 0,
                'status' => 'Pending',
            ]);
        }

        return response()->json(['customer_order_id' => $customerOrder->id], 201);
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
        $order->verified_by = $request->input('verified_by');
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

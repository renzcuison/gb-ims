<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\CustomerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // List all orders (only for admin or own orders for users)
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['stock', 'customerOrder']);

        if ($user->role !== 'admin') {
            $query->whereHas('customerOrder', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $orders = $query->get();

        return response()->json($orders);
    }

    // Create a new customer order
    public function createCustomerOrder(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'order_code' => 'required|string|unique:customer_orders,order_code',
            'customer_name' => 'required|string',
            'shipping_address' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
            'payment_method' => 'required|string',
            'total_price' => 'required|numeric',
            'status' => 'sometimes|string',
        ]);

        $validated['user_id'] = $user->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'Pending';
        }

        $customerOrder = CustomerOrder::create($validated);

        return response()->json($customerOrder, 201);
    }

    // Add or update an order item under a customer order
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_order_id' => 'required|exists:customer_orders,id',
            'stock_id' => 'required|string|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();

        // Check ownership unless admin
        $customerOrder = CustomerOrder::findOrFail($validated['customer_order_id']);
        if ($user->role !== 'admin' && $customerOrder->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $stock = Stock::findOrFail($validated['stock_id']);
            $validated['price_per_unit'] = $stock->price_per_unit;

            $existingOrder = Order::where('customer_order_id', $validated['customer_order_id'])
                ->where('stock_id', $validated['stock_id'])
                ->first();

            if ($existingOrder) {
                $existingOrder->quantity += $validated['quantity'];
                $existingOrder->save();
                $order = $existingOrder;
            } else {
                $order = Order::create($validated);
            }

            DB::commit();

            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create or update order.', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $order = Order::with(['stock', 'customerOrder'])->findOrFail($id);

        if ($user->role !== 'admin' && $order->customerOrder->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = Order::with('customerOrder')->findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $order->customerOrder->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'customer_order_id' => 'required|exists:customer_orders,id',
            'stock_id' => 'required|string|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $stock = Stock::findOrFail($validated['stock_id']);
        $validated['price_per_unit'] = $stock->price_per_unit;

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy($id)
    {
        $order = Order::with('customerOrder')->findOrFail($id);
        $user = Auth::user();

        if ($user->role !== 'admin' && $order->customerOrder->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['items.window'])->latest()->get();
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'delivery_address' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.window_id' => 'required|exists:windows,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('YmdHis'),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'delivery_address' => $validated['delivery_address'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'ordered_at' => now(),
                'total_price' => 0
            ]);

            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $window = \App\Models\Window::find($item['window_id']);
                $itemTotal = $window->price * $item['quantity'];
                $totalPrice += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'window_id' => $item['window_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $window->price,
                    'total_price' => $itemTotal
                ]);
            }

            $order->update(['total_price' => $totalPrice]);
            $order->load(['items.window']);

            DB::commit();
            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.window']);
        return response()->json($order);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'string|max:255',
            'customer_email' => 'email',
            'customer_phone' => 'string',
            'delivery_address' => 'string',
            'status' => 'string|in:pending,processing,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $order->update($validated);
        $order->load(['items.window']);

        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled'
        ]);

        $order->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : null
        ]);

        return response()->json($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(null, 204);
    }
}

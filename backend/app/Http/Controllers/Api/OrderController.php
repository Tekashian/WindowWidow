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
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.window']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->get('date_to'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'ordered_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);
        
        return new JsonResponse($orders);
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
            return new JsonResponse($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse(['error' => 'Failed to create order'], 500);
        }
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.window']);
        return new JsonResponse($order);
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

        return new JsonResponse($order);
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

        return new JsonResponse($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return new JsonResponse(null, 204);
    }
}

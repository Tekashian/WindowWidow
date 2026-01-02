<?php

namespace App\Http\Controllers;

use App\Models\WarehouseDelivery;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseDeliveryController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get warehouse deliveries
     */
    public function index(Request $request)
    {
        $query = WarehouseDelivery::with([
            'productionOrder',
            'batch',
            'shipper',
            'receiver'
        ]);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('productionOrder', function($subQ) use ($search) {
                      $subQ->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter pending/delayed
        if ($request->boolean('pending_only')) {
            $query->pending();
        }

        if ($request->boolean('delayed_only')) {
            $query->delayed();
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('expected_delivery_date', '>=', $request->get('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('expected_delivery_date', '<=', $request->get('date_to'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'expected_delivery_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $deliveries = $query->paginate($perPage);

        return response()->json($deliveries);
    }

    /**
     * Get single delivery
     */
    public function show($id)
    {
        $delivery = WarehouseDelivery::with([
            'productionOrder.windows',
            'batch',
            'shipper',
            'receiver'
        ])->findOrFail($id);

        return response()->json($delivery);
    }

    /**
     * Ship delivery
     */
    public function ship(Request $request, $id)
    {
        $delivery = WarehouseDelivery::findOrFail($id);

        if ($delivery->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending deliveries can be shipped'
            ], 400);
        }

        $delivery->ship(Auth::id());

        return response()->json([
            'message' => 'Delivery shipped successfully',
            'delivery' => $delivery->fresh(['shipper'])
        ]);
    }

    /**
     * Receive delivery
     */
    public function receive(Request $request, $id)
    {
        $delivery = WarehouseDelivery::findOrFail($id);

        if ($delivery->status !== 'in_transit') {
            return response()->json([
                'message' => 'Only in-transit deliveries can be received'
            ], 400);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string'
        ]);

        $delivery->receive(Auth::id());

        if (isset($validated['notes'])) {
            $delivery->update(['notes' => $validated['notes']]);
        }

        // Notify production
        $this->notificationService->notifyProductionDeliveryReceived($delivery->fresh(['batch', 'productionOrder']));

        return response()->json([
            'message' => 'Delivery received successfully',
            'delivery' => $delivery->fresh(['receiver'])
        ]);
    }

    /**
     * Reject delivery
     */
    public function reject(Request $request, $id)
    {
        $delivery = WarehouseDelivery::findOrFail($id);

        $validated = $request->validate([
            'notes' => 'required|string'
        ]);

        $delivery->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['notes'],
            'notes' => $validated['notes']
        ]);

        // Notify production about rejection
        $this->notificationService->notifyProductionDeliveryRejected($delivery->fresh(['batch', 'productionOrder']));

        return response()->json([
            'message' => 'Delivery rejected',
            'delivery' => $delivery
        ]);
    }

    /**
     * Get delivery statistics
     */
    public function statistics()
    {
        $stats = [
            'pending' => WarehouseDelivery::pending()->count(),
            'in_transit' => WarehouseDelivery::inTransit()->count(),
            'delayed' => WarehouseDelivery::delayed()->count(),
            'delivered_today' => WarehouseDelivery::where('status', 'delivered')
                ->whereDate('received_at', today())
                ->count()
        ];

        return response()->json($stats);
    }
}

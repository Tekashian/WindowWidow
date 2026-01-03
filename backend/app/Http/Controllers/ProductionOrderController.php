<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use App\Models\ProductionTimeline;
use App\Models\ProductionBatch;
use App\Models\ProductionMaterial;
use App\Models\ProductionIssue;
use App\Models\Material;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductionOrderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of production orders
     */
    public function index(Request $request)
    {
        $query = ProductionOrder::with(['windows', 'items', 'assignedUser', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter delayed orders
        if ($request->boolean('delayed')) {
            $query->delayed();
        }

        // Filter in progress
        if ($request->boolean('in_progress')) {
            $query->inProgress();
        }

        $orders = $query->paginate($request->get('per_page', 15));

        return new JsonResponse($orders);
    }

    /**
     * Store a newly created production order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Product selection
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            
            // Customer information (optional - can be auto-filled)
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            
            // Delivery information (optional - defaults to warehouse)
            'delivery_address' => 'nullable|string',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_postal_code' => 'nullable|string|max:10',
            'delivery_notes' => 'nullable|string',
            
            // Order settings
            'priority' => 'required|in:low,normal,high,urgent',
            'source_type' => 'required|in:customer_order,stock_replenishment',
            'source_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Get product and company settings
            $product = DB::table('products')->find($validated['product_id']);
            $company = DB::table('company_settings')->first();

            // Use company data as defaults if customer data not provided
            $customerName = $validated['customer_name'] ?? $company->company_name;
            $deliveryAddress = $validated['delivery_address'] ?? $company->warehouse_address;
            $deliveryCity = $validated['delivery_city'] ?? $company->warehouse_city;
            $deliveryPostalCode = $validated['delivery_postal_code'] ?? $company->warehouse_postal_code;

            $order = ProductionOrder::create([
                // Customer info (auto-filled with company data if empty)
                'customer_name' => $customerName,
                'customer_phone' => $validated['customer_phone'] ?? $company->phone,
                'customer_email' => $validated['customer_email'] ?? $company->email,
                
                // Delivery info (defaults to warehouse)
                'delivery_address' => $deliveryAddress,
                'delivery_city' => $deliveryCity,
                'delivery_postal_code' => $deliveryPostalCode,
                'delivery_notes' => $validated['delivery_notes'] ?? null,
                
                // Product info from database
                'product_type' => $product->name,
                'product_description' => $product->description,
                'quantity' => $validated['quantity'],
                'product_specifications' => $product->default_specifications,
                
                // Order details
                'priority' => $validated['priority'],
                'status' => 'pending',
                'source_type' => $validated['source_type'],
                'source_id' => $validated['source_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'created_by' => Auth::id(),
            ]);
                    $order->items()->create($item);
                }
            }

            // Create initial timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => 'pending',
                'notes' => 'Production order created',
                'created_by' => Auth::id()
            ]);

            // Send notification to production team
            $this->notificationService->sendToRole(
                'production',
                'new_production_order',
                "New production order #{$order->order_number} requires confirmation",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'product_type' => $order->product_type,
                    'quantity' => $order->quantity,
                    'priority' => $order->priority
                ],
                $order->priority === 'urgent' ? 'critical' : 'normal'
            );

            DB::commit();

            return new JsonResponse([
                'message' => 'Production order created successfully',
                'order' => $order->load(['windows', 'items', 'assignedUser'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Failed to create production order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified production order
     */
    public function show($id)
    {
        $order = ProductionOrder::with([
            'windows',
            'items.profile',
            'items.glass',
            'assignedUser',
            'creator',
            'timeline.creator',
            'batches',
            'materials.material',
            'issues.reporter',
            'deliveries.batch'
        ])->findOrFail($id);

        return new JsonResponse($order);
    }

    /**
     * Update the specified production order
     */
    public function update(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'priority' => 'sometimes|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_completion_at' => 'nullable|date',
        ]);

        $order->update(array_merge($validated, [
            'updated_by' => Auth::id()
        ]));

        return new JsonResponse([
            'message' => 'Production order updated successfully',
            'order' => $order->load(['windows', 'items', 'assignedUser'])
        ]);
    }

    /**
     * Start production (check and reserve materials)
     */
    public function startProduction(Request $request, $id)
    {
        $order = ProductionOrder::with('materials')->findOrFail($id);

        if ($order->status !== 'pending') {
            return new JsonResponse([
                'message' => 'Only pending orders can be started'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Check materials availability
            $materialsNeeded = $request->validate([
                'materials' => 'required|array',
                'materials.*.material_id' => 'required|exists:materials,id',
                'materials.*.quantity' => 'required|numeric|min:0',
            ])['materials'];

            $insufficientMaterials = [];

            foreach ($materialsNeeded as $materialData) {
                $material = Material::find($materialData['material_id']);
                
                if ($material->quantity < $materialData['quantity']) {
                    $insufficientMaterials[] = [
                        'material' => $material->name,
                        'required' => $materialData['quantity'],
                        'available' => $material->quantity
                    ];
                }
            }

            if (!empty($insufficientMaterials)) {
                DB::rollBack();
                return new JsonResponse([
                    'message' => 'Insufficient materials',
                    'materials' => $insufficientMaterials
                ], 400);
            }

            // Reserve materials
            foreach ($materialsNeeded as $materialData) {
                $material = Material::find($materialData['material_id']);
                
                // Reduce stock
                $material->decrement('quantity', $materialData['quantity']);

                // Create material reservation
                ProductionMaterial::create([
                    'production_order_id' => $order->id,
                    'material_id' => $material->id,
                    'quantity_required' => $materialData['quantity'],
                    'quantity_used' => 0,
                    'reserved_at' => now()
                ]);
            }

            // Update order status
            $order->update([
                'status' => 'materials_reserved',
                'updated_by' => Auth::id()
            ]);

            // Add timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => 'materials_reserved',
                'notes' => 'Materials reserved and production ready to start',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return new JsonResponse([
                'message' => 'Production started successfully',
                'order' => $order->fresh(['materials.material'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Failed to start production',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update production status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,materials_check,materials_reserved,in_progress,quality_check,completed,shipped_to_warehouse,delivered,cancelled,on_hold',
            'notes' => 'nullable|string',
            'delay_reason' => 'nullable|string',
            'estimated_completion' => 'nullable|date'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;

            // Update order
            $order->update([
                'status' => $validated['status'],
                'updated_by' => Auth::id()
            ]);

            // Add timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? "Status changed from {$oldStatus} to {$validated['status']}",
                'estimated_completion' => $validated['estimated_completion'] ?? null,
                'delay_reason' => $validated['delay_reason'] ?? null,
                'created_by' => Auth::id()
            ]);

            // If completed, set actual completion date
            if ($validated['status'] === 'completed' && !$order->actual_completion_at) {
                $order->update(['actual_completion_at' => now()]);
            }

            DB::commit();

            return new JsonResponse([
                'message' => 'Status updated successfully',
                'order' => $order->fresh(['timeline.creator'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report an issue
     */
    public function reportIssue(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);

        $validated = $request->validate([
            'issue_type' => 'required|in:material_shortage,equipment_failure,quality_defect,other',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'impact' => 'required|in:none,minimal,moderate,severe',
            'estimated_delay_hours' => 'nullable|integer|min:0'
        ]);

        $issue = ProductionIssue::create(array_merge($validated, [
            'production_order_id' => $order->id,
            'status' => 'open',
            'reported_by' => Auth::id()
        ]));

        // If critical, put order on hold
        if ($validated['severity'] === 'critical') {
            $order->update([
                'status' => 'on_hold',
                'updated_by' => Auth::id()
            ]);

            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => 'on_hold',
                'notes' => 'Order on hold due to critical issue: ' . $validated['description'],
                'created_by' => Auth::id()
            ]);

            // Notify about critical issue
            $this->notificationService->notifyProductionCriticalIssue($issue->fresh('productionOrder'));
        }

        return new JsonResponse([
            'message' => 'Issue reported successfully',
            'issue' => $issue->load('reporter')
        ], 201);
    }

    /**
     * Create production batch
     */
    public function createBatch(Request $request, $id)
    {
        $order = ProductionOrder::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $order->quantity,
            'quality_notes' => 'nullable|string'
        ]);

        $batch = ProductionBatch::create([
            'production_order_id' => $order->id,
            'quantity' => $validated['quantity'],
            'status' => 'in_production',
            'quality_notes' => $validated['quality_notes'] ?? null,
            'started_at' => now()
        ]);

        return new JsonResponse([
            'message' => 'Batch created successfully',
            'batch' => $batch
        ], 201);
    }

    /**
     * Ship batch to warehouse
     */
    public function shipToWarehouse(Request $request, $id)
    {
        $order = ProductionOrder::with('batches')->findOrFail($id);

        $validated = $request->validate([
            'batch_id' => 'required|exists:production_batches,id',
            'expected_delivery_date' => 'required|date',
            'items' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        $batch = $order->batches()->findOrFail($validated['batch_id']);

        if ($batch->status !== 'ready') {
            return new JsonResponse([
                'message' => 'Only ready batches can be shipped'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create delivery
            $delivery = $order->deliveries()->create([
                'batch_id' => $batch->id,
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => 'pending',
                'items' => $validated['items'],
                'notes' => $validated['notes'] ?? null,
                'shipped_by' => Auth::id(),
                'shipped_at' => now()
            ]);

            // Update batch
            $batch->update([
                'status' => 'shipped',
                'shipped_at' => now()
            ]);

            // Update order status if all batches shipped
            $allBatchesShipped = $order->batches()->where('status', '!=', 'shipped')->count() === 0;
            if ($allBatchesShipped) {
                $order->update([
                    'status' => 'shipped_to_warehouse',
                    'updated_by' => Auth::id()
                ]);

                ProductionTimeline::create([
                    'production_order_id' => $order->id,
                    'status' => 'shipped_to_warehouse',
                    'notes' => 'All batches shipped to warehouse',
                    'created_by' => Auth::id()
                ]);
            }

            DB::commit();

            // Notify warehouse about shipment
            $this->notificationService->notifyWarehouseShipment($delivery->fresh(['batch', 'productionOrder']));

            return new JsonResponse([
                'message' => 'Batch shipped to warehouse successfully',
                'delivery' => $delivery
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Failed to ship batch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get production statistics
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => ProductionOrder::count(),
            'pending' => ProductionOrder::where('status', 'pending')->count(),
            'in_progress' => ProductionOrder::inProgress()->count(),
            'completed' => ProductionOrder::where('status', 'completed')->count(),
            'delayed' => ProductionOrder::delayed()->count(),
            'on_hold' => ProductionOrder::where('status', 'on_hold')->count(),
            'critical_issues' => ProductionIssue::open()->critical()->count(),
        ];

        return new JsonResponse($stats);
    }

    /**
     * Delete production order
     */
    public function destroy($id)
    {
        $order = ProductionOrder::findOrFail($id);

        if ($order->status !== 'pending') {
            return new JsonResponse([
                'message' => 'Only pending orders can be deleted'
            ], 400);
        }

        $order->delete();

        return new JsonResponse([
            'message' => 'Production order deleted successfully'
        ]);
    }

    /**
     * Confirm production order by production team
     */
    public function confirmOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'estimated_completion_at' => 'required|date|after:now',
            'notes' => 'nullable|string'
        ]);

        try {
            $order = ProductionOrder::findOrFail($id);

            $order->confirmByProduction(
                Auth::id(),
                $validated['estimated_completion_at']
            );

            // Create timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => 'confirmed',
                'notes' => $validated['notes'] ?? 'Order confirmed by production team',
                'created_by' => Auth::id()
            ]);

            // Send notification to admin
            $this->notificationService->sendToRole(
                'admin',
                'production_order_confirmed',
                "Production order {$order->order_number} has been confirmed",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'estimated_completion' => $order->estimated_completion_at
                ]
            );

            return new JsonResponse([
                'message' => 'Production order confirmed successfully',
                'order' => $order->fresh(['confirmedBy', 'assignedUser'])
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to confirm order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report delay on production order
     */
    public function reportDelay(Request $request, $id)
    {
        $validated = $request->validate([
            'delay_reason' => 'required|string|max:500',
            'revised_completion_at' => 'required|date|after:now'
        ]);

        try {
            $order = ProductionOrder::findOrFail($id);

            $order->reportDelay(
                $validated['delay_reason'],
                $validated['revised_completion_at']
            );

            // Create timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => 'delayed',
                'notes' => "Delay reported: {$validated['delay_reason']}",
                'created_by' => Auth::id()
            ]);

            // Send notification to admin
            $this->notificationService->sendToRole(
                'admin',
                'production_order_delayed',
                "Production order {$order->order_number} is delayed",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'delay_reason' => $validated['delay_reason'],
                    'revised_completion' => $order->revised_completion_at
                ],
                'critical'
            );

            return new JsonResponse([
                'message' => 'Delay reported successfully',
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to report delay',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update production progress
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:materials_check,materials_reserved,in_progress,quality_check,completed',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $order = ProductionOrder::findOrFail($id);
            $oldStatus = $order->status;

            $order->updateProgress(
                $validated['status'],
                $validated['notes'] ?? null
            );

            // Create timeline entry
            ProductionTimeline::create([
                'production_order_id' => $order->id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? "Status changed from {$oldStatus} to {$validated['status']}",
                'created_by' => Auth::id()
            ]);

            // Send notification to admin
            $this->notificationService->sendToRole(
                'admin',
                'production_order_updated',
                "Production order {$order->order_number} status updated",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $validated['status']
                ]
            );

            return new JsonResponse([
                'message' => 'Production progress updated successfully',
                'order' => $order->fresh(['timeline', 'assignedUser'])
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to update progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available products for production
     */
    public function getProducts()
    {
        try {
            $products = DB::table('products')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return new JsonResponse([
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company settings
     */
    public function getCompanySettings()
    {
        try {
            $settings = DB::table('company_settings')->first();

            if (!$settings) {
                return new JsonResponse([
                    'message' => 'Company settings not found'
                ], 404);
            }

            return new JsonResponse([
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Failed to fetch company settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}


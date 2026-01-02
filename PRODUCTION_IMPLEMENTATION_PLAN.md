# 🏭 WindowWidow - Production System Implementation Plan

## 📋 System Overview

### Three Separate Panels
1. **Production Panel** - Manufacturing workflow management
2. **Warehouse Panel** - Logistics and inventory management  
3. **Admin Panel** - Metrics, analytics, and system management

---

## 🎯 PHASE 1: PRODUCTION PANEL (Priority)

### Database Schema Design

#### 1. Enhanced `production_orders` table
```sql
- id
- order_number (unique, auto-generated: PRD-2026-0001)
- source_type (enum: 'customer_order', 'stock_replenishment')
- source_id (nullable, references orders.id or internal request)
- status (enum with detailed workflow)
- priority (enum: 'low', 'normal', 'high', 'urgent')
- assigned_to (user_id, nullable)
- started_at
- estimated_completion_at
- actual_completion_at
- notes
- created_by
- updated_by
- timestamps
```

**Status Flow:**
1. `pending` - Waiting to start
2. `materials_check` - Checking material availability
3. `materials_reserved` - Materials allocated
4. `in_progress` - Active manufacturing
5. `quality_check` - QA inspection
6. `completed` - Ready for warehouse
7. `shipped_to_warehouse` - In transit to warehouse
8. `delivered` - Confirmed by warehouse
9. `cancelled` - Cancelled
10. `on_hold` - Paused (with reason)

#### 2. New `production_timeline` table
```sql
- id
- production_order_id (FK)
- status (same as production_orders.status)
- notes (detailed update)
- estimated_completion (nullable)
- delay_reason (nullable)
- issues (JSON: array of issues)
- created_by (user who made update)
- created_at
- updated_at
```

#### 3. New `production_batches` table
```sql
- id
- production_order_id (FK)
- batch_number (e.g., BATCH-PRD-2026-0001-01)
- quantity
- status (enum: 'in_production', 'quality_check', 'ready', 'shipped', 'rejected')
- quality_check_passed (boolean)
- quality_notes
- started_at
- completed_at
- shipped_at
- timestamps
```

#### 4. New `production_materials` table (materials used)
```sql
- id
- production_order_id (FK)
- material_id (FK to materials)
- quantity_required
- quantity_used (actual usage)
- reserved_at
- used_at
- returned_quantity (unused materials)
- timestamps
```

#### 5. New `production_issues` table
```sql
- id
- production_order_id (FK)
- issue_type (enum: 'material_shortage', 'equipment_failure', 'quality_issue', 'other')
- severity (enum: 'low', 'medium', 'high', 'critical')
- description
- impact (enum: 'no_delay', 'minor_delay', 'major_delay', 'blocking')
- estimated_delay_hours
- status (enum: 'open', 'in_progress', 'resolved', 'escalated')
- reported_by (user_id)
- resolved_by (user_id, nullable)
- resolved_at
- timestamps
```

#### 6. New `warehouse_deliveries` table
```sql
- id
- production_order_id (FK)
- batch_id (FK to production_batches, nullable)
- delivery_number (unique: DEL-2026-0001)
- expected_delivery_date
- actual_delivery_date
- status (enum: 'pending', 'in_transit', 'delivered', 'rejected', 'partial')
- items (JSON: array of items with quantities)
- notes
- shipped_by (production user_id)
- received_by (warehouse user_id, nullable)
- shipped_at
- received_at
- timestamps
```

---

## 🔧 Backend Implementation

### 1. Database Migrations (Priority Order)

**Migration 1:** Enhance production_orders
```php
Schema::table('production_orders', function (Blueprint $table) {
    $table->string('order_number')->unique()->after('id');
    $table->enum('source_type', ['customer_order', 'stock_replenishment'])->after('order_number');
    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
    $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
    $table->foreignId('assigned_to')->nullable()->constrained('users');
    $table->timestamp('started_at')->nullable();
    $table->timestamp('estimated_completion_at')->nullable();
    $table->timestamp('actual_completion_at')->nullable();
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
});
```

**Migration 2-6:** Create new tables (production_timeline, production_batches, etc.)

### 2. Models & Relationships

**ProductionOrder Model:**
```php
class ProductionOrder extends Model {
    protected $fillable = [
        'order_number', 'source_type', 'source_id', 'status', 
        'priority', 'assigned_to', 'started_at', 'estimated_completion_at',
        'actual_completion_at', 'notes', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'actual_completion_at' => 'datetime',
    ];
    
    // Relationships
    public function timeline() { return $this->hasMany(ProductionTimeline::class); }
    public function batches() { return $this->hasMany(ProductionBatch::class); }
    public function materials() { return $this->hasMany(ProductionMaterial::class); }
    public function issues() { return $this->hasMany(ProductionIssue::class); }
    public function deliveries() { return $this->hasMany(WarehouseDelivery::class); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    
    // Scopes
    public function scopeInProgress($query) {
        return $query->whereIn('status', ['materials_check', 'materials_reserved', 'in_progress', 'quality_check']);
    }
    
    public function scopeDelayed($query) {
        return $query->where('estimated_completion_at', '<', now())
                    ->whereNotIn('status', ['completed', 'delivered', 'cancelled']);
    }
    
    // Auto-generate order number
    protected static function boot() {
        parent::boot();
        static::creating(function ($order) {
            $order->order_number = static::generateOrderNumber();
        });
    }
    
    public static function generateOrderNumber() {
        $year = date('Y');
        $lastOrder = static::whereYear('created_at', $year)->latest()->first();
        $nextNumber = $lastOrder ? (intval(substr($lastOrder->order_number, -4)) + 1) : 1;
        return sprintf('PRD-%s-%04d', $year, $nextNumber);
    }
}
```

### 3. API Controllers

**ProductionOrderController:**
```php
class ProductionOrderController extends Controller {
    
    // List all production orders (with filters)
    public function index(Request $request) {
        $query = ProductionOrder::with(['timeline', 'batches', 'issues', 'assignedUser']);
        
        // Filters
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->delayed) {
            $query->delayed();
        }
        
        return $query->orderBy('priority', 'desc')
                    ->orderBy('estimated_completion_at', 'asc')
                    ->paginate(20);
    }
    
    // Create new production order
    public function store(Request $request) {
        $validated = $request->validate([
            'source_type' => 'required|in:customer_order,stock_replenishment',
            'source_id' => 'nullable|integer',
            'items' => 'required|array',
            'priority' => 'required|in:low,normal,high,urgent',
            'estimated_completion_at' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $order = ProductionOrder::create([
                ...$validated,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
            
            // Create items
            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }
            
            // Create initial timeline entry
            $order->timeline()->create([
                'status' => 'pending',
                'notes' => 'Production order created',
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            return response()->json($order->load(['items', 'timeline']), 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }
    
    // Start production (reserve materials)
    public function startProduction(ProductionOrder $order) {
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order cannot be started'], 400);
        }
        
        // Check material availability
        $materialsAvailable = $this->checkMaterialAvailability($order);
        if (!$materialsAvailable) {
            return response()->json(['error' => 'Insufficient materials'], 400);
        }
        
        DB::beginTransaction();
        try {
            // Reserve materials
            $this->reserveMaterials($order);
            
            // Update order status
            $order->update([
                'status' => 'materials_reserved',
                'assigned_to' => auth()->id(),
                'started_at' => now(),
                'updated_by' => auth()->id(),
            ]);
            
            // Add timeline entry
            $order->timeline()->create([
                'status' => 'materials_reserved',
                'notes' => 'Materials reserved, ready to start production',
                'created_by' => auth()->id(),
            ]);
            
            DB::commit();
            return response()->json($order->fresh(['timeline', 'materials']));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to start production'], 500);
        }
    }
    
    // Update production status
    public function updateStatus(Request $request, ProductionOrder $order) {
        $validated = $request->validate([
            'status' => 'required|in:in_progress,quality_check,completed,on_hold,cancelled',
            'notes' => 'required|string',
            'estimated_completion_at' => 'nullable|date',
            'delay_reason' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            
            $order->update([
                'status' => $validated['status'],
                'estimated_completion_at' => $validated['estimated_completion_at'] ?? $order->estimated_completion_at,
                'actual_completion_at' => $validated['status'] === 'completed' ? now() : null,
                'updated_by' => auth()->id(),
            ]);
            
            // Add timeline entry
            $order->timeline()->create([
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'estimated_completion' => $validated['estimated_completion_at'],
                'delay_reason' => $validated['delay_reason'],
                'created_by' => auth()->id(),
            ]);
            
            // Notify warehouse if completed
            if ($validated['status'] === 'completed') {
                event(new ProductionCompleted($order));
            }
            
            DB::commit();
            return response()->json($order->fresh(['timeline']));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update status'], 500);
        }
    }
    
    // Report issue
    public function reportIssue(Request $request, ProductionOrder $order) {
        $validated = $request->validate([
            'issue_type' => 'required|in:material_shortage,equipment_failure,quality_issue,other',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'impact' => 'required|in:no_delay,minor_delay,major_delay,blocking',
            'estimated_delay_hours' => 'nullable|integer',
        ]);
        
        $issue = $order->issues()->create([
            ...$validated,
            'status' => 'open',
            'reported_by' => auth()->id(),
        ]);
        
        // Update timeline
        $order->timeline()->create([
            'status' => $order->status,
            'notes' => "Issue reported: {$validated['description']}",
            'issues' => [$issue->id],
            'created_by' => auth()->id(),
        ]);
        
        // Notify admin if critical
        if ($validated['severity'] === 'critical') {
            event(new CriticalProductionIssue($order, $issue));
        }
        
        return response()->json($issue, 201);
    }
    
    // Create batch
    public function createBatch(Request $request, ProductionOrder $order) {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        
        $batch = $order->batches()->create([
            'batch_number' => $this->generateBatchNumber($order),
            'quantity' => $validated['quantity'],
            'status' => 'in_production',
            'started_at' => now(),
        ]);
        
        return response()->json($batch, 201);
    }
    
    // Ship to warehouse
    public function shipToWarehouse(Request $request, ProductionOrder $order) {
        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Order must be completed first'], 400);
        }
        
        $validated = $request->validate([
            'batch_ids' => 'required|array',
            'expected_delivery_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            $delivery = $order->deliveries()->create([
                'delivery_number' => $this->generateDeliveryNumber(),
                'expected_delivery_date' => $validated['expected_delivery_date'],
                'status' => 'pending',
                'items' => $this->prepareDeliveryItems($order, $validated['batch_ids']),
                'notes' => $validated['notes'],
                'shipped_by' => auth()->id(),
                'shipped_at' => now(),
            ]);
            
            // Update order status
            $order->update([
                'status' => 'shipped_to_warehouse',
                'updated_by' => auth()->id(),
            ]);
            
            // Add timeline
            $order->timeline()->create([
                'status' => 'shipped_to_warehouse',
                'notes' => "Shipped to warehouse: {$delivery->delivery_number}",
                'created_by' => auth()->id(),
            ]);
            
            // Notify warehouse
            event(new DeliveryShipped($delivery));
            
            DB::commit();
            return response()->json($delivery, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to ship to warehouse'], 500);
        }
    }
}
```

---

## 🎨 Frontend Implementation - Production Panel

### Component Architecture

```
frontend/src/views/production/
├── ProductionDashboard.vue          # Main dashboard with metrics
├── ProductionOrdersList.vue         # List of all orders
├── ProductionOrderDetails.vue       # Single order detail with timeline
├── ProductionTimeline.vue           # Visual timeline component
├── ProductionBatchManagement.vue    # Batch creation/management
├── ProductionIssues.vue             # Issue tracking
├── ProductionShipment.vue           # Ship to warehouse
└── components/
    ├── OrderCard.vue
    ├── TimelineItem.vue
    ├── StatusBadge.vue
    ├── PriorityIndicator.vue
    ├── ProgressBar.vue
    └── IssueAlert.vue
```

### Key Features

1. **Dashboard Overview:**
   - Active orders count
   - Orders in each status
   - Delayed orders alert
   - Today's completions
   - Critical issues
   - Production efficiency metrics

2. **Order Management:**
   - Kanban board view (by status)
   - List view with filters
   - Start production button
   - Update status with notes
   - Report issues
   - Create batches

3. **Timeline View:**
   - Visual representation of order progress
   - Status changes with timestamps
   - User who made changes
   - Delay indicators
   - Issue markers

4. **Batch Management:**
   - Create batches for large orders
   - Track batch status
   - Quality check approval
   - Reject/reprocess batches

5. **Shipping Interface:**
   - Select completed batches
   - Set expected delivery date
   - Add shipping notes
   - Generate delivery number
   - Confirm shipment

---

## 🔄 Workflow Examples

### Scenario 1: Normal Production Flow
```
1. Order created (pending)
2. Production starts → reserves materials (materials_reserved)
3. Production begins (in_progress)
4. Batches created and tracked
5. Quality check (quality_check)
6. Approved (completed)
7. Ship to warehouse (shipped_to_warehouse)
8. Warehouse confirms (delivered)
```

### Scenario 2: With Issues
```
1. Order created (pending)
2. Production starts (materials_reserved)
3. Production begins (in_progress)
4. Issue reported: Equipment failure (status: on_hold)
5. Issue resolved → resume production (in_progress)
6. New estimated completion time updated
7. Quality check (quality_check)
8. Approved (completed)
9. Ship to warehouse (shipped_to_warehouse)
```

---

## 🚀 Next Steps (Priority Order)

### Week 1: Backend Foundation
- [ ] Create all database migrations
- [ ] Build models with relationships
- [ ] Implement ProductionOrderController
- [ ] Add status validation logic
- [ ] Create seeder for test data

### Week 2: Frontend Production Panel
- [ ] Build ProductionDashboard
- [ ] Implement ProductionOrdersList with filters
- [ ] Create ProductionTimeline component
- [ ] Add status update interface
- [ ] Implement issue reporting

### Week 3: Advanced Features
- [ ] Batch management interface
- [ ] Shipping to warehouse flow
- [ ] Real-time notifications
- [ ] Material reservation system
- [ ] Quality check workflow

### Week 4: Polish & Testing
- [ ] Add loading states
- [ ] Error handling
- [ ] Mobile optimization
- [ ] User permissions
- [ ] Integration testing

---

## 📊 Success Metrics

1. **Production Efficiency:**
   - Average time from start to completion
   - On-time delivery rate
   - Issue resolution time

2. **Quality:**
   - Batch rejection rate
   - Rework percentage
   - Customer satisfaction

3. **Communication:**
   - Average time for status updates
   - Issue reporting speed
   - Warehouse coordination

---

This plan provides a **solid foundation** for the Production panel. Once approved, we'll implement it step by step, then move to Warehouse and Admin panels.

Ready to start implementation? 🚀

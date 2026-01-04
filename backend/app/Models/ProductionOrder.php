<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\ProductionOrderCompleted;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'source_type',
        'source_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'delivery_address',
        'delivery_city',
        'delivery_postal_code',
        'delivery_notes',
        'product_type',
        'product_description',
        'quantity',
        'product_specifications',
        'status',
        'priority',
        'notes',
        'started_at',
        'started_by',
        'production_time_hours',
        'estimated_warehouse_delivery_date',
        'estimated_completion_at',
        'actual_completion_at',
        'completed_at',
        'confirmed_by_production',
        'confirmed_at',
        'confirmed_by',
        'is_delayed',
        'delay_reason',
        'revised_completion_at',
        'assigned_to',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'estimated_warehouse_delivery_date' => 'datetime',
        'estimated_completion_at' => 'datetime',
        'actual_completion_at' => 'datetime',
        'completed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'revised_completion_at' => 'datetime',
        'product_specifications' => 'array',
        'confirmed_by_production' => 'boolean',
        'is_delayed' => 'boolean',
    ];

    // Relationships
    public function timeline()
    {
        return $this->hasMany(ProductionTimeline::class)->orderBy('created_at', 'desc');
    }

    public function batches()
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionMaterial::class);
    }

    public function issues()
    {
        return $this->hasMany(ProductionIssue::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WarehouseDelivery::class);
    }

    public function windows()
    {
        return $this->belongsToMany(Window::class, 'production_order_items')
            ->withPivot('quantity', 'status')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [
            'materials_check', 
            'materials_reserved', 
            'in_progress', 
            'quality_check'
        ]);
    }

    public function scopeDelayed($query)
    {
        return $query->where('estimated_completion_at', '<', now())
                    ->whereNotIn('status', ['completed', 'delivered', 'cancelled']);
    }

    public function scopeByPriority($query, $priority = null)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')");
    }

    // Auto-generate order number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber()
    {
        $year = date('Y');
        $lastOrder = static::whereYear('created_at', $year)
                          ->latest('id')
                          ->first();
        
        $nextNumber = $lastOrder ? (intval(substr($lastOrder->order_number, -4)) + 1) : 1;
        return sprintf('PRD-%s-%04d', $year, $nextNumber);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'w_trakcie',
            'started_at' => now(),
        ]);

        // Zmień status wszystkich okien na "w produkcji"
        foreach ($this->items as $item) {
            if ($item->window) {
                $item->window->update(['status' => 'w_produkcji']);
            }
        }
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'zakonczone',
            'completed_at' => now(),
        ]);

        // Zmień status wszystkich okien na "gotowe"
        foreach ($this->items as $item) {
            if ($item->window) {
                $item->window->update(['status' => 'gotowe']);
            }
        }

        // Wywołaj event
        event(new ProductionOrderCompleted($this));
    }

    public function confirmByProduction($userId, $estimatedCompletion = null): void
    {
        $this->update([
            'confirmed_by_production' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'estimated_completion_at' => $estimatedCompletion ?? $this->estimated_completion_at,
        ]);
    }

    public function reportDelay($reason, $revisedCompletion): void
    {
        $this->update([
            'is_delayed' => true,
            'delay_reason' => $reason,
            'revised_completion_at' => $revisedCompletion,
        ]);
    }

    public function updateProgress($status, $notes = null): void
    {
        $data = ['status' => $status];
        if ($notes) {
            $data['notes'] = $notes;
        }
        $this->update($data);
    }
}

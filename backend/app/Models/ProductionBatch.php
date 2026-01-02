<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'batch_number',
        'quantity',
        'status',
        'quality_check_passed',
        'quality_notes',
        'started_at',
        'completed_at',
        'shipped_at'
    ];

    protected $casts = [
        'quality_check_passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WarehouseDelivery::class, 'batch_id');
    }

    // Scopes
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeInProduction($query)
    {
        return $query->whereIn('status', ['in_production', 'quality_check']);
    }

    // Auto-generate batch number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($batch) {
            if (!$batch->batch_number) {
                $batch->batch_number = static::generateBatchNumber($batch->production_order_id);
            }
        });
    }

    public static function generateBatchNumber($productionOrderId)
    {
        $order = ProductionOrder::find($productionOrderId);
        $batchCount = static::where('production_order_id', $productionOrderId)->count();
        return sprintf('%s-B%02d', $order->order_number, $batchCount + 1);
    }
}

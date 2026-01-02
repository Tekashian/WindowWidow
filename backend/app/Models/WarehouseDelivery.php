<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'batch_id',
        'delivery_number',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'items',
        'notes',
        'rejection_reason',
        'shipped_by',
        'received_by',
        'shipped_at',
        'received_at'
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'items' => 'array',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelayed($query)
    {
        return $query->where('status', 'pending')
            ->where('expected_delivery_date', '<', now());
    }

    // Auto-generate delivery number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($delivery) {
            if (!$delivery->delivery_number) {
                $delivery->delivery_number = static::generateDeliveryNumber();
            }
        });
    }

    public static function generateDeliveryNumber()
    {
        $year = now()->year;
        $lastDelivery = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNumber = $lastDelivery 
            ? intval(substr($lastDelivery->delivery_number, -4)) + 1 
            : 1;
            
        return sprintf('DEL-%d-%04d', $year, $nextNumber);
    }

    // Methods
    public function ship($userId)
    {
        $this->update([
            'status' => 'in_transit',
            'shipped_by' => $userId,
            'shipped_at' => now()
        ]);
    }

    public function receive($userId)
    {
        $this->update([
            'status' => 'delivered',
            'received_by' => $userId,
            'received_at' => now(),
            'actual_delivery_date' => now()
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'material_id',
        'quantity_required',
        'quantity_used',
        'returned_quantity',
        'reserved_at',
        'used_at'
    ];

    protected $casts = [
        'quantity_required' => 'decimal:2',
        'quantity_used' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
        'reserved_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    // Accessors
    public function getWasteQuantityAttribute()
    {
        return $this->quantity_used - $this->quantity_required;
    }

    public function getUsagePercentageAttribute()
    {
        if ($this->quantity_required == 0) return 0;
        return ($this->quantity_used / $this->quantity_required) * 100;
    }
}

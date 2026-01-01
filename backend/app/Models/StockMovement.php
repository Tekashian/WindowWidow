<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'type',
        'quantity',
        'reason',
        'user_id',
        'production_order_id'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }
}

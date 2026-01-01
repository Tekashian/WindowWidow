<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'window_id',
        'quantity',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function window()
    {
        return $this->belongsTo(Window::class);
    }
}

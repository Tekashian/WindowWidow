<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'status',
        'total_price',
        'notes',
        'ordered_at',
        'completed_at'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'ordered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function windows()
    {
        return $this->belongsToMany(Window::class, 'order_items')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}

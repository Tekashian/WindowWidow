<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Window extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'width',
        'height',
        'profile_id',
        'glass_id',
        'price',
        'color',
        'status',
        'description',
        'is_active',
        'stock_quantity',
        'min_stock_level'
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
    ];

    const STATUS_PROJEKT = 'projekt';
    const STATUS_W_PRODUKCJI = 'w_produkcji';
    const STATUS_GOTOWE = 'gotowe';
    const STATUS_WYDANE = 'wydane';

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function glass()
    {
        return $this->belongsTo(Glass::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity', 'unit_price', 'total_price')
            ->withTimestamps();
    }
}

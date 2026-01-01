<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'manufacturer',
        'type',
        'material',
        'color',
        'price_per_meter',
        'is_active'
    ];

    protected $casts = [
        'price_per_meter' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function windows()
    {
        return $this->hasMany(Window::class);
    }
}

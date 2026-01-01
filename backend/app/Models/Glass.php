<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Glass extends Model
{
    use HasFactory;

    protected $table = 'glasses';

    protected $fillable = [
        'name',
        'type',
        'thickness',
        'u_value',
        'price_per_sqm',
        'description',
        'is_active'
    ];

    protected $casts = [
        'thickness' => 'integer',
        'u_value' => 'decimal:2',
        'price_per_sqm' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function windows()
    {
        return $this->hasMany(Window::class);
    }
}

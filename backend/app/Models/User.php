<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    const ROLE_ADMIN = 'admin';
    const ROLE_MAGAZYNIER = 'magazynier';
    const ROLE_PRODUKCJA = 'produkcja';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMagazynier(): bool
    {
        return $this->role === self::ROLE_MAGAZYNIER;
    }

    public function isProdukcja(): bool
    {
        return $this->role === self::ROLE_PRODUKCJA;
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'assigned_to');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}

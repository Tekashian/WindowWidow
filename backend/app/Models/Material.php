<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'unit',
        'current_stock',
        'min_stock',
        'price_per_unit',
        'supplier',
        'is_active'
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    public function addStock(float $quantity, string $reason = null): void
    {
        $this->increment('current_stock', $quantity);
        
        $this->stockMovements()->create([
            'type' => 'in',
            'quantity' => $quantity,
            'reason' => $reason ?? 'Dostawa',
            'user_id' => auth()->id(),
        ]);
    }

    public function removeStock(float $quantity, string $reason = null): void
    {
        if ($quantity > $this->current_stock) {
            throw new \Exception("Niewystarczająca ilość materiału: {$this->name}. Dostępne: {$this->current_stock}, Wymagane: {$quantity}");
        }

        $this->decrement('current_stock', $quantity);
        
        $this->stockMovements()->create([
            'type' => 'out',
            'quantity' => $quantity,
            'reason' => $reason ?? 'Zużycie produkcyjne',
            'user_id' => auth()->id() ?? null,
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\ProductionOrderCompleted;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'status',
        'priority',
        'notes',
        'started_at',
        'completed_at',
        'assigned_to'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function windows()
    {
        return $this->belongsToMany(Window::class, 'production_order_items')
            ->withPivot('quantity', 'status')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'w_trakcie',
            'started_at' => now(),
        ]);

        // Zmień status wszystkich okien na "w produkcji"
        foreach ($this->items as $item) {
            $item->window->update(['status' => 'w_produkcji']);
        }
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'zakonczone',
            'completed_at' => now(),
        ]);

        // Zmień status wszystkich okien na "gotowe"
        foreach ($this->items as $item) {
            $item->window->update(['status' => 'gotowe']);
        }

        // Wywołaj event
        event(new ProductionOrderCompleted($this));
    }
}

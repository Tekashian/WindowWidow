<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionTimeline extends Model
{
    use HasFactory;

    protected $table = 'production_timeline';

    protected $fillable = [
        'production_order_id',
        'status',
        'notes',
        'estimated_completion',
        'delay_reason',
        'issues',
        'created_by'
    ];

    protected $casts = [
        'estimated_completion' => 'datetime',
        'issues' => 'array',
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('Y-m-d H:i');
    }
}

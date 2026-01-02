<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'issue_type',
        'severity',
        'description',
        'impact',
        'estimated_delay_hours',
        'status',
        'reported_by',
        'resolved_by',
        'resolved_at'
    ];

    protected $casts = [
        'estimated_delay_hours' => 'integer',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->whereIn('severity', ['critical', 'high']);
    }

    // Methods
    public function isCritical()
    {
        return in_array($this->severity, ['critical', 'high']);
    }

    public function resolve($userId)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId,
            'resolved_at' => now()
        ]);
    }
}

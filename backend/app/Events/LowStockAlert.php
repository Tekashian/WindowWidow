<?php

namespace App\Events;

use App\Models\Material;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Material $material;

    public function __construct(Material $material)
    {
        $this->material = $material;
    }
}

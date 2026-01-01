<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use Illuminate\Support\Facades\Log;

class NotifyLowStock
{
    public function handle(LowStockAlert $event): void
    {
        $material = $event->material;
        
        Log::warning("Niski stan magazynowy", [
            'material' => $material->name,
            'current_stock' => $material->current_stock,
            'min_stock' => $material->min_stock,
            'unit' => $material->unit,
        ]);

        // Tutaj możesz dodać:
        // - wysyłanie alertów do magazyniera
        // - automatyczne zamówienie u dostawcy
        // - powiadomienia SMS/Email
    }
}

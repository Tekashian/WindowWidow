<?php

namespace App\Listeners;

use App\Events\ProductionOrderCompleted;
use Illuminate\Support\Facades\Log;

class NotifyProductionOrderCompleted
{
    public function handle(ProductionOrderCompleted $event): void
    {
        $order = $event->productionOrder;
        
        Log::info("Zlecenie produkcyjne zakończone", [
            'order_number' => $order->order_number,
            'completed_at' => $order->completed_at,
            'items_count' => $order->items->count(),
        ]);

        // Tutaj możesz dodać:
        // - wysyłanie emaili
        // - powiadomienia push
        // - aktualizację systemu ERP
        // - generowanie raportów
    }
}

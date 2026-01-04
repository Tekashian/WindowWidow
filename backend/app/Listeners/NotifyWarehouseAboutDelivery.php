<?php

namespace App\Listeners;

use App\Events\ProductionStarted;
use App\Models\Notification;
use App\Models\User;

class NotifyWarehouseAboutDelivery
{
    public function handle(ProductionStarted $event): void
    {
        $productionOrder = $event->productionOrder;
        
        // Sprawdź czy jest ustawiona przewidywana data dostawy
        if (!$productionOrder->estimated_warehouse_delivery_date) {
            return;
        }

        // Znajdź użytkowników magazynu
        $warehouseUsers = User::where('role', 'warehouse')
            ->orWhere('role', 'admin')
            ->get();

        $deliveryDate = $productionOrder->estimated_warehouse_delivery_date->format('d.m.Y H:i');
        
        // Utwórz powiadomienia dla każdego użytkownika magazynu
        foreach ($warehouseUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'warehouse_delivery',
                'title' => 'Nadchodząca dostawa z produkcji',
                'message' => "Zlecenie {$productionOrder->order_number} zostało rozpoczęte. Przewidywana data dostawy do magazynu: {$deliveryDate}",
                'data' => [
                    'production_order_id' => $productionOrder->id,
                    'order_number' => $productionOrder->order_number,
                    'estimated_delivery' => $productionOrder->estimated_warehouse_delivery_date->toISOString(),
                ],
                'is_read' => false,
            ]);
        }
    }
}

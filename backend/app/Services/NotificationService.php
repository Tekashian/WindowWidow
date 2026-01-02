<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for specific user
     */
    public function createForUser($userId, array $data)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $data['type'] ?? 'system',
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'icon' => $data['icon'] ?? null,
            'link' => $data['link'] ?? null,
        ]);
    }

    /**
     * Create notification for all users
     */
    public function createForAll(array $data)
    {
        $users = User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->createForUser($user->id, $data);
        }

        return $notifications;
    }

    /**
     * Create notification for users by role
     */
    public function createForRole($role, array $data)
    {
        $users = User::where('role', $role)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->createForUser($user->id, $data);
        }

        return $notifications;
    }

    /**
     * Notify warehouse about production shipment
     */
    public function notifyWarehouseShipment($delivery)
    {
        return $this->createForRole('warehouse', [
            'type' => 'warehouse',
            'title' => '📦 Nowa dostawa w drodze',
            'message' => "Partia {$delivery->batch->batch_number} z zamówienia {$delivery->productionOrder->order_number} została wysłana.",
            'data' => [
                'delivery_id' => $delivery->id,
                'batch_id' => $delivery->batch_id,
                'production_order_id' => $delivery->production_order_id,
            ],
            'priority' => 'high',
            'icon' => '🚚',
            'link' => '/warehouse',
        ]);
    }

    /**
     * Notify production about delivery received
     */
    public function notifyProductionDeliveryReceived($delivery)
    {
        return $this->createForRole('production', [
            'type' => 'production',
            'title' => '✅ Dostawa odebrana',
            'message' => "Dostawa {$delivery->delivery_number} została odebrana przez magazyn.",
            'data' => [
                'delivery_id' => $delivery->id,
                'production_order_id' => $delivery->production_order_id,
            ],
            'priority' => 'normal',
            'icon' => '✅',
            'link' => "/production/orders/{$delivery->production_order_id}",
        ]);
    }

    /**
     * Notify production about delivery rejected
     */
    public function notifyProductionDeliveryRejected($delivery)
    {
        return $this->createForRole('production', [
            'type' => 'production',
            'title' => '❌ Dostawa odrzucona',
            'message' => "Dostawa {$delivery->delivery_number} została odrzucona. Powód: {$delivery->rejection_reason}",
            'data' => [
                'delivery_id' => $delivery->id,
                'production_order_id' => $delivery->production_order_id,
            ],
            'priority' => 'critical',
            'icon' => '❌',
            'link' => "/production/orders/{$delivery->production_order_id}",
        ]);
    }

    /**
     * Notify about critical production issue
     */
    public function notifyProductionCriticalIssue($issue)
    {
        return $this->createForRole('production', [
            'type' => 'production',
            'title' => '🚨 Problem krytyczny',
            'message' => "Krytyczny problem w zleceniu {$issue->productionOrder->order_number}: {$issue->issue}",
            'data' => [
                'issue_id' => $issue->id,
                'production_order_id' => $issue->production_order_id,
            ],
            'priority' => 'critical',
            'icon' => '🚨',
            'link' => "/production/orders/{$issue->production_order_id}",
        ]);
    }

    /**
     * Notify about low stock material
     */
    public function notifyLowStock($material)
    {
        return $this->createForAll([
            'type' => 'system',
            'title' => '⚠️ Niski stan magazynowy',
            'message' => "Materiał {$material->name} ma niski stan: {$material->quantity} {$material->unit}",
            'data' => [
                'material_id' => $material->id,
            ],
            'priority' => 'high',
            'icon' => '⚠️',
            'link' => '/materials',
        ]);
    }

    /**
     * Notify about new window added
     */
    public function notifyNewWindow($window)
    {
        return $this->createForAll([
            'type' => 'admin',
            'title' => '🪟 Nowy typ okna',
            'message' => "Dodano nowy typ okna: {$window->name} ({$window->type})",
            'data' => [
                'window_id' => $window->id,
            ],
            'priority' => 'low',
            'icon' => '🪟',
            'link' => '/windows',
        ]);
    }

    /**
     * Notify about production order completed
     */
    public function notifyProductionCompleted($productionOrder)
    {
        return $this->createForRole('warehouse', [
            'type' => 'production',
            'title' => '🎉 Zlecenie ukończone',
            'message' => "Zlecenie {$productionOrder->order_number} zostało ukończone i jest gotowe do odbioru.",
            'data' => [
                'production_order_id' => $productionOrder->id,
            ],
            'priority' => 'normal',
            'icon' => '🎉',
            'link' => '/warehouse',
        ]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }
}

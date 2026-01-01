<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ProductionOrder;

class ProductionOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Wszyscy mogą przeglądać
    }

    public function view(User $user, ProductionOrder $productionOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'produkcja']);
    }

    public function update(User $user, ProductionOrder $productionOrder): bool
    {
        // Admin lub przypisana osoba
        return $user->role === 'admin' || $productionOrder->assigned_to === $user->id;
    }

    public function delete(User $user, ProductionOrder $productionOrder): bool
    {
        return $user->role === 'admin';
    }

    public function start(User $user, ProductionOrder $productionOrder): bool
    {
        return in_array($user->role, ['admin', 'produkcja']);
    }

    public function complete(User $user, ProductionOrder $productionOrder): bool
    {
        return in_array($user->role, ['admin', 'produkcja']);
    }
}

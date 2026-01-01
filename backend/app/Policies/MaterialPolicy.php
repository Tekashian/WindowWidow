<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Material;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'magazynier', 'produkcja']);
    }

    public function view(User $user, Material $material): bool
    {
        return in_array($user->role, ['admin', 'magazynier', 'produkcja']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'magazynier']);
    }

    public function update(User $user, Material $material): bool
    {
        return in_array($user->role, ['admin', 'magazynier']);
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->role === 'admin';
    }

    public function manageStock(User $user): bool
    {
        return in_array($user->role, ['admin', 'magazynier']);
    }
}

<?php

namespace App\Policies;

use App\Models\OrderDetail;
use App\Models\User;

class OrderDetailPolicy
{
    public function updateQuantity(User $user, OrderDetail $detail): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true)
            && $detail->order()->where('status', 'pending')->exists();
    }

    public function updateNote(User $user, OrderDetail $detail): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true)
            && $detail->order()->where('status', 'pending')->exists();
    }

    public function delete(User $user, OrderDetail $detail): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true)
            && $detail->order()->where('status', 'pending')->exists();
    }
}
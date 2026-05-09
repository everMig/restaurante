<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function addItem(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true);
    }

    public function applyDiscount(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true) && $order->status === 'pending';
    }

    public function moveTable(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true) && $order->status === 'pending';
    }

    public function checkout(User $user, Order $order): bool
    {
        return in_array($user->role, ['admin', 'cashier'], true) && $order->status === 'pending';
    }
}
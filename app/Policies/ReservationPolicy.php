<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true);
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true);
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        return in_array($user->role, ['admin', 'cashier', 'waiter'], true);
    }
}
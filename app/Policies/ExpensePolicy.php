<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'cashier'], true);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->role === 'admin' || (int) $expense->user_id === (int) $user->id;
    }
}
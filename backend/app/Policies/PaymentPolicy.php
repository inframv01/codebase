<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Operator) {
            return $payment->deliveryRequest->accepted_by_operator_id === $user->id;
        }

        return $payment->deliveryRequest->user_id === $user->id;
    }
}

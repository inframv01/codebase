<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\DeliveryRequest;
use App\Models\User;

class DeliveryRequestPolicy
{
    public function viewAnyOperator(User $user): bool
    {
        return $user->role?->isOperatorOrAdmin() === true;
    }

    public function quote(User $user, DeliveryRequest $deliveryRequest): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $deliveryRequest->accepted_by_operator_id === null || $deliveryRequest->accepted_by_operator_id === $user->id;
    }

    public function accept(User $user, DeliveryRequest $deliveryRequest): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $deliveryRequest->accepted_by_operator_id === null || $deliveryRequest->accepted_by_operator_id === $user->id;
    }

    public function stage(User $user, DeliveryRequest $deliveryRequest): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $deliveryRequest->accepted_by_operator_id === $user->id;
    }

    public function verifyPayment(User $user, DeliveryRequest $deliveryRequest): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $deliveryRequest->accepted_by_operator_id === $user->id;
    }

    public function rejectPayment(User $user, DeliveryRequest $deliveryRequest): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $deliveryRequest->accepted_by_operator_id === $user->id;
    }

    public function viewCustomer(User $user, DeliveryRequest $deliveryRequest): bool
    {
        return $deliveryRequest->user_id === $user->id;
    }

    public function cancel(User $user, DeliveryRequest $deliveryRequest): bool
    {
        return $deliveryRequest->user_id === $user->id;
    }

    public function uploadPayment(User $user, DeliveryRequest $deliveryRequest): bool
    {
        return $deliveryRequest->user_id === $user->id;
    }
}

<?php

namespace App\Policies;

use App\Models\SavedAddress;
use App\Models\User;

class SavedAddressPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, SavedAddress $savedAddress): bool
    {
        return $savedAddress->user_id === $user->id;
    }

    public function update(User $user, SavedAddress $savedAddress): bool
    {
        return $savedAddress->user_id === $user->id;
    }

    public function delete(User $user, SavedAddress $savedAddress): bool
    {
        return $savedAddress->user_id === $user->id;
    }
}

<?php

namespace App\Policies;

use App\Models\Ad;
use App\Models\User;

class AdPolicy
{
    public function view(?User $user, Ad $ad): bool
    {
        if ($ad->status === 'approved') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $user->id === $ad->user_id || $user->role === 'admin';
    }

    public function update(User $user, Ad $ad): bool
    {
        return $user->id === $ad->user_id;
    }

    public function delete(User $user, Ad $ad): bool
    {
        return $user->id === $ad->user_id;
    }

    public function changeStatus(User $user, Ad $ad, string $status): bool
    {
        return $user->id === $ad->user_id && $ad->ownerCanTransitionTo($status);
    }
}

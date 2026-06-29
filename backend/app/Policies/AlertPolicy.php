<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Alert $alert): bool
    {
        return $this->owns($user, $alert);
    }

    public function update(User $user, Alert $alert): bool
    {
        return $this->owns($user, $alert);
    }

    private function owns(User $user, Alert $alert): bool
    {
        return $user->id === $alert->user_id;
    }
}

<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

class BusinessPolicy
{
    public function view(User $user, Business $business): bool
    {
        return $user->business_id === $business->id;
    }

    public function update(User $user, Business $business): bool
    {
        return $this->view($user, $business);
    }
}

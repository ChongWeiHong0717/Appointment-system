<?php

namespace App\Policies;

use App\Models\SpecialDate;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessResources;

class SpecialDatePolicy
{
    use AuthorizesBusinessResources;

    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, SpecialDate $specialDate): bool
    {
        return $this->owns($user, $specialDate);
    }

    public function delete(User $user, SpecialDate $specialDate): bool
    {
        return $this->owns($user, $specialDate);
    }
}

<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessResources;

class ServicePolicy
{
    use AuthorizesBusinessResources;

    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Service $service): bool
    {
        return $this->owns($user, $service);
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Service $service): bool
    {
        return $this->owns($user, $service);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->owns($user, $service);
    }
}

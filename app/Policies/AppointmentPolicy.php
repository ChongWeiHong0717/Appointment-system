<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\Policies\Concerns\AuthorizesBusinessResources;

class AppointmentPolicy
{
    use AuthorizesBusinessResources;

    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->owns($user, $appointment);
    }

    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->owns($user, $appointment);
    }
}

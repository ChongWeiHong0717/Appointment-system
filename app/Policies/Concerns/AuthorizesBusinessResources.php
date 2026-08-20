<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesBusinessResources
{
    protected function owns(User $user, Model $model): bool
    {
        return $user->business_id !== null && $user->business_id === $model->getAttribute('business_id');
    }
}

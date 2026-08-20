<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('platform');

        abort_unless(
            $user
                && $user->is_active
                && $user->business_id === null
                && $user->role === UserRole::PlatformAdmin,
            Response::HTTP_FORBIDDEN,
            'An active platform administrator account is required.'
        );

        return $next($request);
    }
}

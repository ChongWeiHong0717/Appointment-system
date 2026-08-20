<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->business_id && $user->role === UserRole::BusinessAdmin,
            Response::HTTP_FORBIDDEN,
            'A business administrator account is required.'
        );

        $business = $user->business;
        abort_unless($business?->is_active, Response::HTTP_FORBIDDEN, 'This business is not active.');

        View::share('currentBusiness', $business);

        return $next($request);
    }
}

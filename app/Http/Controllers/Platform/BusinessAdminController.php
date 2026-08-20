<?php

namespace App\Http\Controllers\Platform;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\ResetBusinessAdminPasswordRequest;
use App\Http\Requests\Platform\StoreBusinessAdminRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessAdminController extends Controller
{
    public function store(StoreBusinessAdminRequest $request, Business $business): RedirectResponse
    {
        $business->users()->create([
            'name' => $request->string('name')->toString(),
            'email' => str($request->string('email'))->lower()->toString(),
            'password' => $request->string('password')->toString(),
            'role' => UserRole::BusinessAdmin,
            'is_active' => true,
        ]);

        return back()->with('success', 'Business administrator created.');
    }

    public function resetPassword(
        ResetBusinessAdminPasswordRequest $request,
        Business $business,
        User $admin
    ): RedirectResponse {
        $admin = $this->businessAdmin($business, $admin);
        $admin->forceFill([
            'password' => $request->string('password')->toString(),
            'remember_token' => null,
        ])->save();
        $this->forgetSessions($admin);

        return back()->with('success', "Password reset for {$admin->name}.");
    }

    public function updateStatus(Request $request, Business $business, User $admin): RedirectResponse
    {
        $admin = $this->businessAdmin($business, $admin);
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $active = (bool) $validated['is_active'];
        $admin->update(['is_active' => $active]);

        if (! $active) {
            $admin->forceFill(['remember_token' => null])->save();
            $this->forgetSessions($admin);
        }

        return back()->with('success', $active ? 'Business administrator enabled.' : 'Business administrator disabled.');
    }

    public function destroy(Business $business, User $admin): RedirectResponse
    {
        $admin = $this->businessAdmin($business, $admin);
        $name = $admin->name;
        $this->forgetSessions($admin);
        $admin->delete();

        return back()->with('success', "{$name} was permanently deleted.");
    }

    private function businessAdmin(Business $business, User $admin): User
    {
        abort_unless(
            $admin->business_id === $business->id && $admin->role === UserRole::BusinessAdmin,
            404
        );

        return $admin;
    }

    private function forgetSessions(User $admin): void
    {
        DB::table('sessions')->where('user_id', $admin->id)->delete();
    }
}

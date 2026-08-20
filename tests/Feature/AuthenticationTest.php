<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_login_but_cannot_view_dashboard(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in to your business');
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_business_admin_can_log_in_and_out(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->for($business)->create(['password' => 'secret-password']);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee($business->name);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'nobody@example.test',
            'password' => 'incorrect',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_disabled_business_admin_cannot_log_in(): void
    {
        $business = Business::factory()->create();
        $admin = User::factory()->for($business)->create([
            'password' => 'secret-password',
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}

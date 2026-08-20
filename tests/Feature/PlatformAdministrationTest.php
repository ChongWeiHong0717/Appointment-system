<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_and_business_logins_are_separate(): void
    {
        $business = Business::factory()->create();
        $businessAdmin = User::factory()->for($business)->create(['password' => 'business-password']);
        $platformAdmin = User::factory()->platformAdmin()->create(['password' => 'platform-password']);

        $this->get(route('platform.login'))->assertOk()->assertSee('Platform sign in');

        $this->post(route('platform.login.store'), [
            'email' => $businessAdmin->email,
            'password' => 'business-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest('platform');

        $this->post(route('login.store'), [
            'email' => $platformAdmin->email,
            'password' => 'platform-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('platform.login.store'), [
            'email' => $platformAdmin->email,
            'password' => 'platform-password',
        ])->assertRedirect(route('platform.businesses.index'));
        $this->assertAuthenticatedAs($platformAdmin, 'platform');
    }

    public function test_platform_admin_can_create_a_business_with_safe_defaults(): void
    {
        $platformAdmin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($platformAdmin, 'platform')->post(route('platform.businesses.store'), [
            'name' => 'North Star Clinic',
            'slug' => 'north-star-clinic',
            'email' => 'hello@northstar.test',
            'phone' => '+60 12-555 0101',
            'address' => 'Kuala Lumpur',
            'timezone' => 'Asia/Kuala_Lumpur',
            'booking_interval_minutes' => 30,
        ]);

        $business = Business::query()->where('slug', 'north-star-clinic')->firstOrFail();
        $response->assertRedirect(route('platform.businesses.show', $business));
        $this->assertTrue($business->is_active);
        $this->assertDatabaseHas('website_settings', ['business_id' => $business->id]);
        $this->assertDatabaseCount('business_hours', 7);
    }

    public function test_platform_admin_can_manage_only_the_selected_business_accounts(): void
    {
        $platformAdmin = User::factory()->platformAdmin()->create();
        $business = Business::factory()->create();
        $otherBusiness = Business::factory()->create();
        $otherAdmin = User::factory()->for($otherBusiness)->create();

        $this->actingAs($platformAdmin, 'platform')->post(route('platform.businesses.admins.store', $business), [
            'name' => 'Business Owner',
            'email' => 'owner@example.test',
            'password' => 'temporary-passphrase',
            'password_confirmation' => 'temporary-passphrase',
        ])->assertRedirect();

        $admin = User::query()->where('email', 'owner@example.test')->firstOrFail();
        $this->assertSame($business->id, $admin->business_id);
        $this->assertSame(UserRole::BusinessAdmin, $admin->role);

        $this->actingAs($platformAdmin, 'platform')->put(route('platform.businesses.admins.password', [$business, $admin]), [
            'password' => 'replacement-passphrase',
            'password_confirmation' => 'replacement-passphrase',
        ])->assertRedirect();
        $admin->refresh();
        $this->assertTrue(Hash::check('replacement-passphrase', $admin->password));
        $this->assertNull($admin->remember_token);

        $this->actingAs($platformAdmin, 'platform')->patch(route('platform.businesses.admins.status', [$business, $admin]), [
            'is_active' => false,
        ])->assertRedirect();
        $this->assertFalse($admin->fresh()->is_active);

        $this->actingAs($platformAdmin, 'platform')->patch(route('platform.businesses.admins.status', [$business, $otherAdmin]), [
            'is_active' => false,
        ])->assertNotFound();
        $this->assertTrue($otherAdmin->fresh()->is_active);

        $this->actingAs($platformAdmin, 'platform')->delete(route('platform.businesses.admins.destroy', [$business, $admin]))
            ->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_suspension_blocks_business_login_and_preserves_records(): void
    {
        $platformAdmin = User::factory()->platformAdmin()->create();
        $business = Business::factory()->create();
        $businessAdmin = User::factory()->for($business)->create(['password' => 'business-password']);
        $category = Category::factory()->for($business)->create();
        $service = Service::factory()->for($business)->for($category)->create();
        Appointment::factory()->for($business)->for($service)->create();

        $this->actingAs($platformAdmin, 'platform')->delete(route('platform.businesses.destroy', $business))
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('businesses', ['id' => $business->id, 'is_active' => false]);
        $this->assertDatabaseHas('appointments', ['business_id' => $business->id]);

        Auth::shouldUse('web');
        $this->post(route('login.store'), [
            'email' => $businessAdmin->email,
            'password' => 'business-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get(route('public.home', $business))->assertNotFound();
    }

    public function test_business_without_history_can_be_permanently_deleted(): void
    {
        $platformAdmin = User::factory()->platformAdmin()->create();
        $business = Business::factory()->create();
        $businessAdmin = User::factory()->for($business)->create();

        $this->actingAs($platformAdmin, 'platform')->delete(route('platform.businesses.destroy', $business))
            ->assertRedirect(route('platform.businesses.index'));

        $this->assertDatabaseMissing('businesses', ['id' => $business->id]);
        $this->assertDatabaseMissing('users', ['id' => $businessAdmin->id]);
    }
}

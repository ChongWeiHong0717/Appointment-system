<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private User $admin;

    private Category $category;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-20 08:00', 'Asia/Kuala_Lumpur'));
        $this->business = Business::factory()->create();
        $this->admin = User::factory()->for($this->business)->create();
        $this->category = Category::factory()->for($this->business)->create();
        $this->service = Service::factory()->for($this->business)->for($this->category)->create([
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
        $this->business->businessHours()->create([
            'day_of_week' => 4,
            'opens_at' => '09:00',
            'closes_at' => '18:00',
            'is_closed' => false,
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_admin_can_manage_own_catalog_but_cannot_open_another_business_record(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'New Category',
            'description' => 'A customer-friendly category.',
            'is_active' => '1',
            'sort_order' => '2',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'business_id' => $this->business->id,
            'name' => 'New Category',
            'slug' => 'new-category',
        ]);

        $otherBusiness = Business::factory()->create();
        $otherCategory = Category::factory()->for($otherBusiness)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $otherCategory))
            ->assertNotFound();
    }

    public function test_service_cannot_be_assigned_to_another_business_category(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherCategory = Category::factory()->for($otherBusiness)->create();

        $this->actingAs($this->admin)->post(route('admin.services.store'), [
            'category_id' => $otherCategory->id,
            'name' => 'Cross tenant service',
            'description' => null,
            'price' => 50,
            'duration_minutes' => 60,
            'is_active' => 1,
            'sort_order' => 0,
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('services', ['name' => 'Cross tenant service']);
    }

    public function test_manual_booking_uses_the_shared_availability_rules(): void
    {
        $payload = $this->appointmentPayload();

        $this->actingAs($this->admin)
            ->post(route('admin.appointments.store'), $payload)
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->post(route('admin.appointments.store'), $payload)
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_status_actions_follow_the_allowed_lifecycle(): void
    {
        $appointment = $this->createAppointment();

        $this->actingAs($this->admin)
            ->post(route('admin.appointments.check-in', $appointment))
            ->assertSessionHas('success');
        $this->assertSame(AppointmentStatus::CheckedIn, $appointment->refresh()->status);
        $this->assertNotNull($appointment->checked_in_at);

        $this->actingAs($this->admin)
            ->post(route('admin.appointments.complete', $appointment))
            ->assertSessionHas('success');
        $this->assertSame(AppointmentStatus::Completed, $appointment->refresh()->status);
        $this->assertNotNull($appointment->completed_at);

        $this->from(route('admin.appointments.show', $appointment))
            ->actingAs($this->admin)
            ->post(route('admin.appointments.check-in', $appointment))
            ->assertRedirect(route('admin.appointments.show', $appointment))
            ->assertSessionHasErrors('status');
    }

    public function test_check_in_search_ignores_phone_formatting(): void
    {
        $appointment = $this->createAppointment([
            'customer_name' => 'John Searchable',
            'customer_phone' => '012-345 6789',
            'customer_phone_normalized' => PhoneNumber::normalize('012-345 6789'),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.check-in.index', ['q' => '0123456789']))
            ->assertOk()
            ->assertSee('John Searchable')
            ->assertSee(route('admin.check-in.store', $appointment));
    }

    public function test_business_settings_update_only_the_authenticated_tenant(): void
    {
        $otherBusiness = Business::factory()->create(['name' => 'Other Business']);

        $this->actingAs($this->admin)->put(route('admin.website.business.update'), [
            'name' => 'Updated Business',
            'phone' => '03-1234 5678',
            'email' => 'updated@example.test',
            'description' => 'Updated description',
        ])->assertSessionHas('success');

        $this->assertSame('Updated Business', $this->business->refresh()->name);
        $this->assertSame('Other Business', $otherBusiness->refresh()->name);
    }

    private function appointmentPayload(): array
    {
        return [
            'service_id' => $this->service->id,
            'appointment_date' => '2026-08-20',
            'start_time' => '09:00',
            'customer_name' => 'Admin Booking',
            'customer_phone' => '012-999 8888',
            'customer_email' => 'admin.booking@example.test',
            'customer_notes' => null,
            'internal_notes' => 'Created by phone.',
        ];
    }

    private function createAppointment(array $overrides = []): Appointment
    {
        return Appointment::create([
            'business_id' => $this->business->id,
            'service_id' => $this->service->id,
            'customer_name' => 'Lifecycle Customer',
            'customer_phone' => '012-000 0000',
            'customer_phone_normalized' => '0120000000',
            'appointment_date' => '2026-08-20',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => AppointmentStatus::Booked,
            ...$overrides,
        ]);
    }
}

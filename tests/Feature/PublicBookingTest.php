<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-20 08:00', 'Asia/Kuala_Lumpur'));
        $this->business = Business::factory()->create(['slug' => 'test-studio']);
        $category = Category::factory()->for($this->business)->create(['is_active' => true]);
        $this->service = Service::factory()->for($this->business)->for($category)->create([
            'name' => 'Signature Service',
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

    public function test_public_homepage_uses_the_business_catalog(): void
    {
        $this->get(route('public.home', $this->business))
            ->assertOk()
            ->assertSee($this->business->name)
            ->assertSee('Signature Service');
    }

    public function test_customer_can_book_and_view_a_signed_confirmation(): void
    {
        $response = $this->post(route('public.booking.store', $this->business), $this->validBooking());

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'business_id' => $this->business->id,
            'service_id' => $this->service->id,
            'customer_phone_normalized' => '0123456789',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Appointment confirmed')
            ->assertSee('Signature Service');
    }

    public function test_an_existing_appointment_prevents_double_booking(): void
    {
        $this->post(route('public.booking.store', $this->business), $this->validBooking())->assertRedirect();

        $this->from(route('public.booking.create', $this->business))
            ->post(route('public.booking.store', $this->business), $this->validBooking())
            ->assertRedirect(route('public.booking.create', $this->business))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_service_from_another_business_is_rejected(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherCategory = Category::factory()->for($otherBusiness)->create();
        $otherService = Service::factory()->for($otherBusiness)->for($otherCategory)->create();

        $this->post(route('public.booking.store', $this->business), [
            ...$this->validBooking(),
            'service_id' => $otherService->id,
        ])->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('appointments', 0);
    }

    private function validBooking(): array
    {
        return [
            'service_id' => $this->service->id,
            'appointment_date' => '2026-08-20',
            'start_time' => '09:00',
            'customer_name' => 'Jamie Tan',
            'customer_phone' => '012-345 6789',
            'customer_email' => 'jamie@example.test',
            'customer_notes' => 'First visit',
        ];
    }
}

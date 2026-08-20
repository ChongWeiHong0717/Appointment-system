<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-20 08:00', 'Asia/Kuala_Lumpur'));
        $this->business = Business::factory()->create(['booking_interval_minutes' => 30]);
        $category = Category::factory()->for($this->business)->create();
        $this->service = Service::factory()->for($this->business)->for($category)->create(['duration_minutes' => 90]);
        $this->business->businessHours()->create([
            'day_of_week' => 4,
            'opens_at' => '09:00',
            'closes_at' => '12:00',
            'is_closed' => false,
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_slots_respect_service_duration_and_closing_time(): void
    {
        $slots = app(AvailabilityService::class)->slots($this->business, $this->service, '2026-08-20');

        $this->assertSame(['09:00', '09:30', '10:00', '10:30'], array_column($slots, 'value'));
    }

    public function test_overlapping_appointments_are_removed_from_availability(): void
    {
        $phone = '012-345 6789';
        Appointment::create([
            'business_id' => $this->business->id,
            'service_id' => $this->service->id,
            'customer_name' => 'Test Customer',
            'customer_phone' => $phone,
            'customer_phone_normalized' => PhoneNumber::normalize($phone),
            'appointment_date' => '2026-08-20',
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => AppointmentStatus::Booked,
        ]);

        $slots = app(AvailabilityService::class)->slots($this->business, $this->service, '2026-08-20');

        $this->assertSame([], $slots);
    }

    public function test_special_closed_date_overrides_weekly_hours(): void
    {
        $this->business->specialDates()->create([
            'date' => '2026-08-20',
            'is_closed' => true,
        ]);

        $this->assertSame([], app(AvailabilityService::class)->slots($this->business, $this->service, '2026-08-20'));
    }
}

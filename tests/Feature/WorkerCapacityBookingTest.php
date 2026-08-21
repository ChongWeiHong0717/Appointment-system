<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Service;
use App\Models\Worker;
use App\Services\AppointmentBookingService;
use App\Services\StaffingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkerCapacityBookingTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-20 08:00', 'Asia/Kuala_Lumpur'));
        $this->business = Business::factory()->create([
            'slug' => 'capacity-test',
            'timezone' => 'Asia/Kuala_Lumpur',
            'booking_interval_minutes' => 30,
        ]);
        $this->category = Category::factory()->for($this->business)->create(['is_active' => true]);
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

    public function test_parallel_bookings_use_actual_worker_capacity_across_services(): void
    {
        $grooming = $this->service('Full Grooming', 60, 2);
        $nails = $this->service('Nail Trim', 30, 1);
        $workers = collect(range(1, 5))->map(fn ($number) => $this->worker("Worker {$number}", [$grooming, $nails]));

        $booking = app(AppointmentBookingService::class);
        $booking->create($this->business, $grooming, $this->bookingData('Customer A', '09:00'));
        $booking->create($this->business, $grooming, $this->bookingData('Customer B', '09:00'));
        $booking->create($this->business, $nails, $this->bookingData('Customer C', '09:00'));

        $this->assertDatabaseCount('appointments', 3);
        $this->assertDatabaseCount('appointment_worker', 5);
        $this->assertSame(5, $workers->count());

        $this->expectException(ValidationException::class);
        $booking->create($this->business, $nails, $this->bookingData('Customer D', '09:00'));
    }

    public function test_only_qualified_workers_count_toward_a_service(): void
    {
        $service = $this->service('Specialist Treatment', 60, 2);
        $this->worker('Qualified One', [$service]);
        $this->worker('Not Qualified', []);

        $this->expectException(ValidationException::class);
        app(AppointmentBookingService::class)->create(
            $this->business,
            $service,
            $this->bookingData('Jamie Tan', '09:00')
        );
    }

    public function test_absence_automatically_reassigns_an_appointment_when_replacement_exists(): void
    {
        $service = $this->service('Two Person Service', 60, 2);
        $first = $this->worker('First', [$service]);
        $second = $this->worker('Second', [$service]);
        $third = $this->worker('Third', [$service]);

        $appointment = app(AppointmentBookingService::class)->create(
            $this->business,
            $service,
            $this->bookingData('Jamie Tan', '09:00')
        );

        $appointment->refresh()->load('workers');
        $this->assertTrue($appointment->workers->contains('id', $first->id));
        $this->assertTrue($appointment->workers->contains('id', $second->id));

        app(StaffingService::class)->markFullDayAbsent($first, '2026-08-20', 'Sick');

        $appointment->refresh()->load('workers');
        $this->assertFalse($appointment->workers->contains('id', $first->id));
        $this->assertTrue($appointment->workers->contains('id', $second->id));
        $this->assertTrue($appointment->workers->contains('id', $third->id));
        $this->assertTrue(app(StaffingService::class)->status($appointment)['healthy']);
    }

    public function test_absence_keeps_booking_but_reports_conflict_when_no_replacement_exists(): void
    {
        $service = $this->service('Two Person Service', 60, 2);
        $first = $this->worker('First', [$service]);
        $this->worker('Second', [$service]);

        $appointment = app(AppointmentBookingService::class)->create(
            $this->business,
            $service,
            $this->bookingData('Jamie Tan', '09:00')
        );

        app(StaffingService::class)->markFullDayAbsent($first, '2026-08-20', 'Emergency leave');

        $appointment->refresh();
        $status = app(StaffingService::class)->status($appointment);

        $this->assertSame('booked', $appointment->status->value);
        $this->assertFalse($status['healthy']);
        $this->assertSame(1, $status['assigned']);
        $this->assertSame(1, $status['missing']);
    }

    public function test_worker_from_another_business_never_satisfies_capacity(): void
    {
        $service = $this->service('Private Service', 60, 1);
        $this->worker('Local Unqualified Worker', []);

        $otherBusiness = Business::factory()->create();
        $otherWorker = Worker::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Business Worker',
            'is_active' => true,
        ]);
        // Intentionally create an invalid cross-tenant qualification at the DB
        // level. Availability must still scope candidates to this business.
        $otherWorker->services()->attach($service->id);

        $this->expectException(ValidationException::class);
        app(AppointmentBookingService::class)->create(
            $this->business,
            $service,
            $this->bookingData('Jamie Tan', '09:00')
        );
    }

    private function service(string $name, int $duration, int $workersRequired): Service
    {
        return Service::factory()->for($this->business)->for($this->category)->create([
            'name' => $name,
            'duration_minutes' => $duration,
            'workers_required' => $workersRequired,
            'is_active' => true,
        ]);
    }

    /** @param array<int, Service> $services */
    private function worker(string $name, array $services): Worker
    {
        $worker = Worker::create([
            'business_id' => $this->business->id,
            'name' => $name,
            'is_active' => true,
        ]);
        $worker->services()->sync(collect($services)->pluck('id')->all());

        return $worker;
    }

    private function bookingData(string $customer, string $time): array
    {
        return [
            'appointment_date' => '2026-08-20',
            'start_time' => $time,
            'customer_name' => $customer,
            'customer_phone' => '012-345 6789',
            'customer_email' => strtolower(str_replace(' ', '.', $customer)).'@example.test',
            'customer_notes' => null,
        ];
    }
}

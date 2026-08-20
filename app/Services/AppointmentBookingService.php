<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentBookingService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function create(Business $business, Service $service, array $data): Appointment
    {
        abort_unless($service->business_id === $business->id, 404);

        return DB::transaction(function () use ($business, $service, $data) {
            Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();

            if (! $this->availability->isAvailable($business, $service, $data['appointment_date'], $data['start_time'])) {
                throw ValidationException::withMessages([
                    'start_time' => 'That time is no longer available. Please choose another slot.',
                ]);
            }

            $start = CarbonImmutable::parse(
                $data['appointment_date'].' '.$data['start_time'],
                $business->timezone
            );

            return Appointment::create([
                'business_id' => $business->id,
                'service_id' => $service->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_phone_normalized' => PhoneNumber::normalize($data['customer_phone']),
                'customer_email' => $data['customer_email'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'appointment_date' => $start->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $start->addMinutes($service->duration_minutes)->format('H:i:s'),
                'status' => AppointmentStatus::Booked,
            ]);
        }, 3);
    }
}

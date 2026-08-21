<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentLifecycleService
{
    public function __construct(private readonly StaffingService $staffing) {}

    public function checkIn(Appointment $appointment): Appointment
    {
        return $this->transition($appointment, [AppointmentStatus::Booked], AppointmentStatus::CheckedIn, [
            'checked_in_at' => now(),
        ]);
    }

    public function complete(Appointment $appointment): Appointment
    {
        return $this->transition($appointment, [AppointmentStatus::CheckedIn], AppointmentStatus::Completed, [
            'completed_at' => now(),
        ]);
    }

    public function cancel(Appointment $appointment): Appointment
    {
        return $this->transition($appointment, [AppointmentStatus::Booked, AppointmentStatus::CheckedIn], AppointmentStatus::Cancelled, [
            'cancelled_at' => now(),
        ]);
    }

    public function markNoShow(Appointment $appointment): Appointment
    {
        return $this->transition($appointment, [AppointmentStatus::Booked], AppointmentStatus::NoShow);
    }

    private function transition(
        Appointment $appointment,
        array $allowedFrom,
        AppointmentStatus $to,
        array $timestamps = []
    ): Appointment {
        return DB::transaction(function () use ($appointment, $allowedFrom, $to, $timestamps) {
            // Use the same business lock as booking/absence changes so a worker
            // cannot be claimed while staffing capacity is being released.
            $business = Business::query()->whereKey($appointment->business_id)->lockForUpdate()->firstOrFail();
            $locked = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);

            if (! in_array($locked->status, $allowedFrom, true)) {
                throw ValidationException::withMessages([
                    'status' => "A {$locked->status->label()} appointment cannot be changed to {$to->label()}.",
                ]);
            }

            $locked->update(['status' => $to, ...$timestamps]);

            if (in_array($to, [AppointmentStatus::Completed, AppointmentStatus::Cancelled, AppointmentStatus::NoShow], true)) {
                $this->staffing->reconcileBusinessDate($business, $locked->appointment_date->format('Y-m-d'));
            }

            return $locked->refresh();
        }, 3);
    }
}

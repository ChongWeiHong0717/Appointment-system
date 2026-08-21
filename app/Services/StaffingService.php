<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Worker;
use App\Models\WorkerAbsence;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StaffingService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * @return array{managed: bool, healthy: bool, required: int, assigned: int, missing: int, workers: Collection}
     */
    public function status(Appointment $appointment): array
    {
        $appointment->loadMissing(['business', 'service', 'workers.services', 'workers.absences']);
        $required = max(1, (int) $appointment->service->workers_required);

        if (! $this->availability->usesWorkerCapacity($appointment->business)) {
            return [
                'managed' => false,
                'healthy' => true,
                'required' => $required,
                'assigned' => 0,
                'missing' => 0,
                'workers' => collect(),
            ];
        }

        if (! in_array($appointment->status, [AppointmentStatus::Booked, AppointmentStatus::CheckedIn], true)) {
            return [
                'managed' => true,
                'healthy' => true,
                'required' => $required,
                'assigned' => $appointment->workers->count(),
                'missing' => 0,
                'workers' => $appointment->workers->values(),
            ];
        }

        $validWorkers = $appointment->workers
            ->filter(fn (Worker $worker) => $this->workerIsValidForAppointment($worker, $appointment))
            ->values();
        $assigned = $validWorkers->count();

        return [
            'managed' => true,
            'healthy' => $assigned >= $required,
            'required' => $required,
            'assigned' => $assigned,
            'missing' => max(0, $required - $assigned),
            'workers' => $validWorkers,
        ];
    }

    /**
     * Remove invalid workers from an active appointment and fill any missing
     * slots with the least-loaded qualified workers that are free.
     */
    public function reconcileAppointment(Appointment $appointment): array
    {
        $appointment->loadMissing(['business', 'service', 'workers.services', 'workers.absences']);

        if (! in_array($appointment->status, [AppointmentStatus::Booked, AppointmentStatus::CheckedIn], true)) {
            return $this->status($appointment);
        }

        if (! $this->availability->usesWorkerCapacity($appointment->business)) {
            return $this->status($appointment);
        }

        $validWorkerIds = $appointment->workers
            ->filter(fn (Worker $worker) => $this->workerIsValidForAppointment($worker, $appointment))
            ->pluck('id')
            ->values();
        $invalidWorkerIds = $appointment->workers->pluck('id')->diff($validWorkerIds);

        if ($invalidWorkerIds->isNotEmpty()) {
            $appointment->workers()->detach($invalidWorkerIds->all());
        }

        $required = max(1, (int) $appointment->service->workers_required);
        if ($validWorkerIds->count() > $required) {
            $excessWorkerIds = $validWorkerIds->slice($required)->all();
            $appointment->workers()->detach($excessWorkerIds);
            $validWorkerIds = $validWorkerIds->take($required)->values();
        }

        $missing = max(0, $required - $validWorkerIds->count());

        if ($missing > 0) {
            $candidates = $this->availability->availableWorkers(
                $appointment->business,
                $appointment->service,
                $appointment->appointment_date,
                $appointment->start_time,
                $appointment->id,
                $validWorkerIds->all(),
            );

            $replacementIds = $candidates->take($missing)->pluck('id')->all();
            if ($replacementIds !== []) {
                $appointment->workers()->syncWithoutDetaching($replacementIds);
            }
        }

        $appointment->unsetRelation('workers');

        return $this->status($appointment);
    }

    public function markFullDayAbsent(Worker $worker, string $date, ?string $reason = null): WorkerAbsence
    {
        return DB::transaction(function () use ($worker, $date, $reason) {
            Business::query()->whereKey($worker->business_id)->lockForUpdate()->firstOrFail();

            $absence = WorkerAbsence::query()->firstOrCreate(
                [
                    'business_id' => $worker->business_id,
                    'worker_id' => $worker->id,
                    'date' => $date,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
                ['reason' => $reason]
            );

            if ($reason !== null && $absence->reason !== $reason) {
                $absence->update(['reason' => $reason]);
            }

            $appointments = $worker->appointments()
                ->whereDate('appointment_date', $date)
                ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
                ->orderBy('start_time')
                ->get();

            foreach ($appointments as $appointment) {
                $this->reconcileAppointment($appointment);
            }

            return $absence;
        }, 3);
    }

    public function restoreAbsence(WorkerAbsence $absence): void
    {
        DB::transaction(function () use ($absence) {
            $business = Business::query()->whereKey($absence->business_id)->lockForUpdate()->firstOrFail();
            $date = $absence->date->toDateString();
            $absence->delete();

            $this->reconcileBusinessDate($business, $date);
        }, 3);
    }

    public function reconcileBusinessDate(Business $business, string $date): void
    {
        if (! $this->availability->usesWorkerCapacity($business)) {
            return;
        }

        $appointments = $business->appointments()
            ->whereDate('appointment_date', $date)
            ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
            ->orderBy('start_time')
            ->get();

        foreach ($appointments as $appointment) {
            $this->reconcileAppointment($appointment);
        }
    }

    public function reconcileFutureBusiness(Business $business): void
    {
        if (! $this->availability->usesWorkerCapacity($business)) {
            return;
        }

        $today = CarbonImmutable::now($business->timezone)->toDateString();
        $appointments = $business->appointments()
            ->whereDate('appointment_date', '>=', $today)
            ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        foreach ($appointments as $appointment) {
            $this->reconcileAppointment($appointment);
        }
    }

    private function workerIsValidForAppointment(Worker $worker, Appointment $appointment): bool
    {
        if (! $worker->is_active || $worker->business_id !== $appointment->business_id) {
            return false;
        }

        if (! DB::table('worker_service')
            ->where('worker_id', $worker->id)
            ->where('service_id', $appointment->service_id)
            ->exists()) {
            return false;
        }

        $date = $appointment->appointment_date->toDateString();
        $start = substr($appointment->start_time, 0, 8);
        $end = substr($appointment->end_time, 0, 8);

        return ! $worker->absences->contains(function (WorkerAbsence $absence) use ($date, $start, $end) {
            if ($absence->date->toDateString() !== $date) {
                return false;
            }

            if ($absence->starts_at === null || $absence->ends_at === null) {
                return true;
            }

            return $absence->starts_at < $end && $absence->ends_at > $start;
        });
    }
}

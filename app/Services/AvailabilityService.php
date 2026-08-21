<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Business;
use App\Models\Service;
use App\Models\Worker;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AvailabilityService
{
    /**
     * @return array<int, array{value: string, label: string, end: string}>
     */
    public function slots(Business $business, Service $service, CarbonInterface|string $date): array
    {
        $this->guardServiceOwnership($business, $service);
        $day = $this->date($date, $business);

        if ($day->isBefore(CarbonImmutable::now($business->timezone)->startOfDay())) {
            return [];
        }

        $windows = $this->workingWindows($business, $day);
        if ($windows->isEmpty()) {
            return [];
        }

        $workerCapacityEnabled = $this->usesWorkerCapacity($business);
        $appointments = $workerCapacityEnabled
            ? collect()
            : $business->appointments()
                ->whereDate('appointment_date', $day->toDateString())
                ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
                ->get(['start_time', 'end_time']);

        $interval = max(5, $business->booking_interval_minutes);
        $now = CarbonImmutable::now($business->timezone);
        $slots = [];

        foreach ($windows as [$opensAt, $closesAt]) {
            for ($cursor = $opensAt; $cursor->addMinutes($service->duration_minutes)->lte($closesAt); $cursor = $cursor->addMinutes($interval)) {
                $end = $cursor->addMinutes($service->duration_minutes);

                if ($cursor->lte($now)) {
                    continue;
                }

                if ($workerCapacityEnabled) {
                    if ($this->availableWorkersForRange($business, $service, $cursor, $end)->count() < max(1, $service->workers_required)) {
                        continue;
                    }
                } elseif ($this->overlaps($cursor, $end, $appointments, $day)) {
                    continue;
                }

                $slots[] = [
                    'value' => $cursor->format('H:i'),
                    'label' => $cursor->format('g:i A'),
                    'end' => $end->format('H:i'),
                ];
            }
        }

        return $slots;
    }

    public function isAvailable(Business $business, Service $service, CarbonInterface|string $date, string $startTime): bool
    {
        return collect($this->slots($business, $service, $date))
            ->contains('value', substr($startTime, 0, 5));
    }

    public function usesWorkerCapacity(Business $business): bool
    {
        return $business->workers()->where('is_active', true)->exists();
    }

    /**
     * Returns qualified, active, present and unbooked workers ordered by
     * today's workload and then ID so assignment is predictable and balanced.
     *
     * @param  array<int, int>  $excludeWorkerIds
     * @return Collection<int, Worker>
     */
    public function availableWorkers(
        Business $business,
        Service $service,
        CarbonInterface|string $date,
        string $startTime,
        ?int $ignoreAppointmentId = null,
        array $excludeWorkerIds = []
    ): Collection {
        $this->guardServiceOwnership($business, $service);
        $day = $this->date($date, $business);
        $start = $this->atTime($day, $startTime);
        $end = $start->addMinutes($service->duration_minutes);

        return $this->availableWorkersForRange(
            $business,
            $service,
            $start,
            $end,
            $ignoreAppointmentId,
            $excludeWorkerIds
        );
    }

    /**
     * @param  array<int, int>  $excludeWorkerIds
     * @return Collection<int, Worker>
     */
    public function availableWorkersForRange(
        Business $business,
        Service $service,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreAppointmentId = null,
        array $excludeWorkerIds = []
    ): Collection {
        $this->guardServiceOwnership($business, $service);
        $day = $start->setTimezone($business->timezone)->startOfDay();
        $startTime = $start->format('H:i:s');
        $endTime = $end->format('H:i:s');

        return $business->workers()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->whereKey($service->id))
            ->when($excludeWorkerIds !== [], fn ($query) => $query->whereNotIn('workers.id', $excludeWorkerIds))
            ->whereDoesntHave('absences', function ($query) use ($day, $startTime, $endTime) {
                $query->whereDate('date', $day->toDateString())
                    ->where(function ($query) use ($startTime, $endTime) {
                        $query->where(function ($query) {
                            $query->whereNull('starts_at')->orWhereNull('ends_at');
                        })->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->whereNotNull('starts_at')
                                ->whereNotNull('ends_at')
                                ->where('starts_at', '<', $endTime)
                                ->where('ends_at', '>', $startTime);
                        });
                    });
            })
            ->whereDoesntHave('appointments', function ($query) use ($day, $startTime, $endTime, $ignoreAppointmentId) {
                $query->whereDate('appointment_date', $day->toDateString())
                    ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
                    ->when($ignoreAppointmentId, fn ($query) => $query->where('appointments.id', '!=', $ignoreAppointmentId))
                    ->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->withCount(['appointments as day_workload' => function ($query) use ($day, $ignoreAppointmentId) {
                $query->whereDate('appointment_date', $day->toDateString())
                    ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value, AppointmentStatus::Completed->value])
                    ->when($ignoreAppointmentId, fn ($query) => $query->where('appointments.id', '!=', $ignoreAppointmentId));
            }])
            ->orderBy('day_workload')
            ->orderBy('workers.id')
            ->get();
    }

    /**
     * @return Collection<int, array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    public function workingWindows(Business $business, CarbonInterface|string $date): Collection
    {
        $day = $this->date($date, $business);
        $specialDate = $business->specialDates()->whereDate('date', $day->toDateString())->first();

        if ($specialDate) {
            if ($specialDate->is_closed || ! $specialDate->opens_at || ! $specialDate->closes_at) {
                return collect();
            }

            return collect([[$this->atTime($day, $specialDate->opens_at), $this->atTime($day, $specialDate->closes_at)]]);
        }

        return $business->businessHours()
            ->where('day_of_week', $day->dayOfWeek)
            ->where('is_closed', false)
            ->whereNotNull('opens_at')
            ->whereNotNull('closes_at')
            ->orderBy('period_index')
            ->get()
            ->map(fn ($hours) => [$this->atTime($day, $hours->opens_at), $this->atTime($day, $hours->closes_at)])
            ->filter(fn (array $window) => $window[1]->gt($window[0]))
            ->values();
    }

    private function overlaps(CarbonImmutable $start, CarbonImmutable $end, Collection $appointments, CarbonImmutable $day): bool
    {
        return $appointments->contains(function ($appointment) use ($start, $end, $day) {
            $existingStart = $this->atTime($day, $appointment->start_time);
            $existingEnd = $this->atTime($day, $appointment->end_time);

            return $start->lt($existingEnd) && $end->gt($existingStart);
        });
    }

    private function date(CarbonInterface|string $date, Business $business): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->setTimezone($business->timezone)->startOfDay()
            : CarbonImmutable::parse($date, $business->timezone)->startOfDay();
    }

    private function atTime(CarbonImmutable $day, string $time): CarbonImmutable
    {
        [$hour, $minute, $second] = array_pad(array_map('intval', explode(':', $time)), 3, 0);

        return $day->setTime($hour, $minute, $second);
    }

    private function guardServiceOwnership(Business $business, Service $service): void
    {
        if ($service->business_id !== $business->id) {
            throw new InvalidArgumentException('The service does not belong to this business.');
        }
    }
}

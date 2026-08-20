<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Business;
use App\Models\Service;
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

        $appointments = $business->appointments()
            ->whereDate('appointment_date', $day->toDateString())
            ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
            ->get(['start_time', 'end_time']);

        $interval = max(5, $business->booking_interval_minutes);
        $now = CarbonImmutable::now($business->timezone);
        $slots = [];

        foreach ($windows as [$opensAt, $closesAt]) {
            for ($cursor = $opensAt; $cursor->addMinutes($service->duration_minutes)->lte($closesAt); $cursor = $cursor->addMinutes($interval)) {
                $end = $cursor->addMinutes($service->duration_minutes);

                if ($cursor->lte($now) || $this->overlaps($cursor, $end, $appointments, $day)) {
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
        return collect($this->slots($business, $service, $date))->contains('value', substr($startTime, 0, 5));
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

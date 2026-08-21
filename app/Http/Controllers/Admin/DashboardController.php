<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Services\StaffingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        StaffingService $staffingService,
        AvailabilityService $availability
    ): View {
        $business = $request->user()->business;
        $today = now($business->timezone)->toDateString();
        $appointments = $business->appointments()
            ->with(['business', 'service.category', 'workers.services', 'workers.absences'])
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->get();

        $summary = [
            'today' => $appointments->count(),
            'upcoming' => $business->appointments()
                ->whereDate('appointment_date', '>', $today)
                ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
                ->count(),
            'checked_in' => $appointments->where('status', AppointmentStatus::CheckedIn)->count(),
            'completed' => $appointments->where('status', AppointmentStatus::Completed)->count(),
        ];

        $staffingStatuses = $appointments->mapWithKeys(
            fn ($appointment) => [$appointment->id => $staffingService->status($appointment)]
        );
        $workerCapacityEnabled = $availability->usesWorkerCapacity($business);
        $activeWorkers = $business->workers()->where('is_active', true)->count();
        $absentWorkers = $business->workerAbsences()
            ->whereDate('date', $today)
            ->whereHas('worker', fn ($query) => $query->where('is_active', true))
            ->distinct('worker_id')
            ->count('worker_id');
        $staffingSummary = [
            'enabled' => $workerCapacityEnabled,
            'active' => $activeWorkers,
            'absent' => $absentWorkers,
            'present' => max(0, $activeWorkers - $absentWorkers),
            'conflicts' => $staffingStatuses
                ->filter(fn (array $status) => $status['managed'] && ! $status['healthy'])
                ->count(),
        ];

        return view('admin.dashboard', compact(
            'business',
            'appointments',
            'summary',
            'today',
            'staffingStatuses',
            'staffingSummary'
        ));
    }
}

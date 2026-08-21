<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\WorkerRequest;
use App\Models\Worker;
use App\Services\StaffingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(Request $request, StaffingService $staffing): View
    {
        $business = $request->user()->business;
        $selectedDate = $request->filled('date')
            ? $request->validate(['date' => ['date_format:Y-m-d']])['date']
            : now($business->timezone)->toDateString();

        $workers = $business->workers()
            ->with([
                'services' => fn ($query) => $query->orderBy('name'),
                'absences' => fn ($query) => $query->whereDate('date', $selectedDate),
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $appointments = $business->appointments()
            ->with(['business', 'service', 'workers.services', 'workers.absences'])
            ->whereDate('appointment_date', $selectedDate)
            ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::CheckedIn->value])
            ->orderBy('start_time')
            ->get();

        $conflicts = $appointments
            ->mapWithKeys(fn ($appointment) => [$appointment->id => $staffing->status($appointment)])
            ->filter(fn (array $status) => $status['managed'] && ! $status['healthy']);

        $summary = [
            'active' => $workers->where('is_active', true)->count(),
            'absent' => $workers->where('is_active', true)->filter(fn ($worker) => $worker->absences->isNotEmpty())->count(),
            'available' => $workers->where('is_active', true)->filter(fn ($worker) => $worker->absences->isEmpty())->count(),
            'conflicts' => $conflicts->count(),
        ];

        return view('admin.workers.index', compact('business', 'workers', 'selectedDate', 'summary', 'appointments', 'conflicts'));
    }

    public function create(Request $request): View
    {
        $business = $request->user()->business;
        $services = $business->services()->with('category')->orderBy('name')->get();

        return view('admin.workers.create', compact('services'));
    }

    public function store(WorkerRequest $request, StaffingService $staffing): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);
        $data['business_id'] = $business->id;

        DB::transaction(function () use ($business, $data, $serviceIds, $staffing) {
            \App\Models\Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();
            $worker = $business->workers()->create($data);
            $worker->services()->sync($serviceIds);
            $staffing->reconcileFutureBusiness($business);
        }, 3);

        return redirect()->route('admin.workers.index')->with('success', 'Worker added.');
    }

    public function edit(Request $request, int $worker): View
    {
        $business = $request->user()->business;
        $worker = $business->workers()->with('services')->findOrFail($worker);
        $services = $business->services()->with('category')->orderBy('name')->get();

        return view('admin.workers.edit', compact('worker', 'services'));
    }

    public function update(WorkerRequest $request, int $worker, StaffingService $staffing): RedirectResponse
    {
        $business = $request->user()->business;
        $worker = $business->workers()->findOrFail($worker);
        $data = $request->validated();
        $serviceIds = $data['service_ids'] ?? [];
        unset($data['service_ids']);

        DB::transaction(function () use ($business, $worker, $data, $serviceIds, $staffing) {
            \App\Models\Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();
            $worker->update($data);
            $worker->services()->sync($serviceIds);
            $staffing->reconcileFutureBusiness($business);
        }, 3);

        return redirect()->route('admin.workers.index')->with('success', 'Worker updated.');
    }

    public function destroy(Request $request, int $worker, StaffingService $staffing): RedirectResponse
    {
        $business = $request->user()->business;
        $worker = $business->workers()->findOrFail($worker);

        $hasHistory = $worker->appointments()->exists();

        DB::transaction(function () use ($business, $worker, $staffing, $hasHistory) {
            \App\Models\Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();
            if ($hasHistory) {
                $worker->update(['is_active' => false]);
            } else {
                $worker->delete();
            }
            $staffing->reconcileFutureBusiness($business);
        }, 3);

        if ($hasHistory) {
            return redirect()->route('admin.workers.index')
                ->with('success', 'Worker has appointment history, so they were deactivated instead of deleted.');
        }

        return redirect()->route('admin.workers.index')->with('success', 'Worker removed.');
    }

    public function markAbsent(Request $request, int $worker, StaffingService $staffing): RedirectResponse
    {
        $business = $request->user()->business;
        $worker = $business->workers()->findOrFail($worker);
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $staffing->markFullDayAbsent($worker, $validated['date'], $validated['reason'] ?? null);

        return redirect()->route('admin.workers.index', ['date' => $validated['date']])
            ->with('success', "{$worker->name} marked absent. Bookwise reassigned affected appointments where possible.");
    }

    public function restoreAbsence(Request $request, int $worker, int $absence, StaffingService $staffing): RedirectResponse
    {
        $business = $request->user()->business;
        $worker = $business->workers()->findOrFail($worker);
        $absence = $worker->absences()->where('business_id', $business->id)->findOrFail($absence);
        $date = $absence->date->toDateString();

        $staffing->restoreAbsence($absence);

        return redirect()->route('admin.workers.index', ['date' => $date])
            ->with('success', "{$worker->name} restored for {$date}. Staffing was recalculated.");
    }
}

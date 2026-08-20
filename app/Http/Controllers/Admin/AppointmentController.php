<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentBookingService;
use App\Services\AvailabilityService;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Appointment::class);
        $business = $request->user()->business;
        $today = now($business->timezone)->toDateString();
        $scope = in_array($request->string('scope')->toString(), ['today', 'upcoming', 'all'], true)
            ? $request->string('scope')->toString()
            : 'today';
        $query = $business->appointments()->with('service.category');

        match ($scope) {
            'today' => $query->whereDate('appointment_date', $today),
            'upcoming' => $query->whereDate('appointment_date', '>', $today),
            default => null,
        };

        if ($request->filled('date')) {
            $request->validate(['date' => ['date_format:Y-m-d']]);
            $query->whereDate('appointment_date', $request->string('date'));
        }
        if ($status = AppointmentStatus::tryFrom($request->string('status')->toString())) {
            $query->where('status', $status->value);
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->integer('service_id'));
        }
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $phone = PhoneNumber::normalize($search);
            $query->where(function ($query) use ($search, $phone) {
                $query->where('customer_name', 'like', "%{$search}%");
                if ($phone !== '') {
                    $query->orWhere('customer_phone_normalized', 'like', "%{$phone}%");
                }
            });
        }

        $appointments = $query
            ->orderBy('appointment_date', $scope === 'all' ? 'desc' : 'asc')
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();
        $services = $business->services()->orderBy('name')->get();

        return view('admin.appointments.index', compact('appointments', 'services', 'scope'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Appointment::class);
        $business = $request->user()->business;
        $services = $business->services()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('admin.appointments.create', compact('business', 'services'));
    }

    public function slots(Request $request, AvailabilityService $availability): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $business = $request->user()->business;
        $service = $business->services()->where('is_active', true)->findOrFail($validated['service_id']);

        return response()->json(['slots' => $availability->slots($business, $service, $validated['date'])]);
    }

    public function store(AdminAppointmentRequest $request, AppointmentBookingService $booking): RedirectResponse
    {
        $business = $request->user()->business;
        $service = $business->services()->where('is_active', true)->findOrFail($request->integer('service_id'));
        $appointment = $booking->create($business, $service, $request->validated());

        return redirect()->route('admin.appointments.show', $appointment)->with('success', 'Appointment created.');
    }

    public function show(Request $request, int $appointment): View
    {
        $appointment = $request->user()->business->appointments()->with('service.category')->findOrFail($appointment);
        Gate::authorize('view', $appointment);

        return view('admin.appointments.show', compact('appointment'));
    }

    public function update(Request $request, int $appointment): RedirectResponse
    {
        $appointment = $request->user()->business->appointments()->findOrFail($appointment);
        Gate::authorize('update', $appointment);
        $validated = $request->validate(['internal_notes' => ['nullable', 'string', 'max:3000']]);
        $appointment->update($validated);

        return back()->with('success', 'Internal notes updated.');
    }
}

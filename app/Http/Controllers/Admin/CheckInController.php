<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppointmentLifecycleService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CheckInController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->user()->business;
        $today = now($business->timezone)->toDateString();
        $search = trim($request->string('q')->toString());
        $query = $business->appointments()->with('service.category');

        if ($search !== '') {
            $phone = PhoneNumber::normalize($search);
            $query->where(function ($query) use ($search, $phone) {
                $query->where('customer_name', 'like', "%{$search}%");
                if ($phone !== '') {
                    $query->orWhere('customer_phone_normalized', 'like', "%{$phone}%");
                }
            });
        } else {
            $query->whereDate('appointment_date', $today);
        }

        $appointments = $query
            ->orderByRaw('CASE WHEN appointment_date = ? THEN 0 ELSE 1 END', [$today])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->limit(50)
            ->get();

        return view('admin.check-in.index', compact('appointments', 'search', 'today'));
    }

    public function store(Request $request, int $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        $appointment = $request->user()->business->appointments()->findOrFail($appointment);
        Gate::authorize('update', $appointment);
        $lifecycle->checkIn($appointment);

        return back()->with('success', "{$appointment->customer_name} is checked in.");
    }
}

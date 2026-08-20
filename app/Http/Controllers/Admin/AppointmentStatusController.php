<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppointmentLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AppointmentStatusController extends Controller
{
    public function checkIn(Request $request, int $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        $appointment = $this->appointment($request, $appointment);
        $lifecycle->checkIn($appointment);

        return back()->with('success', 'Customer checked in.');
    }

    public function complete(Request $request, int $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        $appointment = $this->appointment($request, $appointment);
        $lifecycle->complete($appointment);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function cancel(Request $request, int $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        $appointment = $this->appointment($request, $appointment);
        $lifecycle->cancel($appointment);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function noShow(Request $request, int $appointment, AppointmentLifecycleService $lifecycle): RedirectResponse
    {
        $appointment = $this->appointment($request, $appointment);
        $lifecycle->markNoShow($appointment);

        return back()->with('success', 'Appointment marked as no show.');
    }

    private function appointment(Request $request, int $appointment)
    {
        $appointment = $request->user()->business->appointments()->findOrFail($appointment);
        Gate::authorize('update', $appointment);

        return $appointment;
    }
}

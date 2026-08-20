<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = $request->user()->business;
        $today = now($business->timezone)->toDateString();
        $appointments = $business->appointments()
            ->with('service.category')
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

        return view('admin.dashboard', compact('business', 'appointments', 'summary', 'today'));
    }
}

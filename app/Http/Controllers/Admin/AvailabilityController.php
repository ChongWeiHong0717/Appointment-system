<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessHoursRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        $business = $request->user()->business;
        $hours = $business->businessHours()->where('period_index', 0)->get()->keyBy('day_of_week');
        $specialDates = $business->specialDates()->orderBy('date')->get();

        return view('admin.availability.index', compact('business', 'hours', 'specialDates'));
    }

    public function update(BusinessHoursRequest $request): RedirectResponse
    {
        $business = $request->user()->business;

        foreach ($request->validated('hours') as $hours) {
            $closed = (bool) $hours['is_closed'];
            $business->businessHours()->updateOrCreate(
                ['day_of_week' => $hours['day_of_week'], 'period_index' => 0],
                [
                    'is_closed' => $closed,
                    'opens_at' => $closed ? null : $hours['opens_at'],
                    'closes_at' => $closed ? null : $hours['closes_at'],
                ]
            );
        }

        return back()->with('success', 'Weekly hours updated.');
    }
}

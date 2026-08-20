<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicBookingRequest;
use App\Models\Business;
use App\Services\AppointmentBookingService;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function create(Business $business, Request $request): View
    {
        abort_unless($business->is_active, 404);

        $setting = $business->websiteSetting()->firstOrNew();
        $categories = $business->categories()
            ->where('is_active', true)
            ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->services->isNotEmpty())
            ->values();
        $selectedServiceId = $request->integer('service') ?: null;

        return view('public.booking.create', compact('business', 'setting', 'categories', 'selectedServiceId'));
    }

    public function slots(Business $business, Request $request, AvailabilityService $availability): JsonResponse
    {
        abort_unless($business->is_active, 404);

        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $service = $business->services()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->findOrFail($validated['service_id']);

        return response()->json([
            'slots' => $availability->slots($business, $service, $validated['date']),
        ]);
    }

    public function store(
        PublicBookingRequest $request,
        Business $business,
        AppointmentBookingService $booking
    ): RedirectResponse {
        $service = $business->services()
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->findOrFail($request->integer('service_id'));
        $appointment = $booking->create($business, $service, $request->validated());

        return redirect(URL::temporarySignedRoute(
            'public.booking.confirmation',
            now()->addHours(12),
            ['business' => $business, 'appointment' => $appointment->id]
        ));
    }

    public function confirmation(Business $business, int $appointment): View
    {
        abort_unless($business->is_active, 404);

        $setting = $business->websiteSetting()->firstOrNew();
        $appointment = $business->appointments()
            ->with('service.category')
            ->findOrFail($appointment);

        return view('public.booking.confirmation', compact('business', 'setting', 'appointment'));
    }
}

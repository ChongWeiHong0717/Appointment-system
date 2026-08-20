<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function show(Business $business): View
    {
        abort_unless($business->is_active, 404);

        $setting = $business->websiteSetting()->firstOrNew();
        $categories = $business->categories()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->where('is_active', true))
            ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $hours = $business->businessHours()->where('period_index', 0)->get()->keyBy('day_of_week');

        return view('public.home', compact('business', 'setting', 'categories', 'hours'));
    }
}

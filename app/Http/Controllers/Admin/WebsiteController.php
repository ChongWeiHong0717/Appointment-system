<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppearanceRequest;
use App\Http\Requests\BusinessInformationRequest;
use App\Http\Requests\HomepageRequest;
use App\Services\ImageStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function editBusiness(Request $request): View
    {
        $business = $request->user()->business;
        Gate::authorize('update', $business);

        return view('admin.website.business', compact('business'));
    }

    public function updateBusiness(BusinessInformationRequest $request, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        Gate::authorize('update', $business);
        $data = $request->validated();
        unset($data['logo'], $data['instagram'], $data['facebook'], $data['website']);
        $data['social_links'] = array_filter([
            'instagram' => $request->validated('instagram'),
            'facebook' => $request->validated('facebook'),
            'website' => $request->validated('website'),
        ]);
        $data['logo_path'] = $images->replace($request->file('logo'), $business->logo_path, "businesses/{$business->id}/brand");
        $business->update($data);

        return back()->with('success', 'Business information updated.');
    }

    public function editHomepage(Request $request): View
    {
        $business = $request->user()->business;
        $setting = $business->websiteSetting()->firstOrCreate([]);

        return view('admin.website.homepage', compact('business', 'setting'));
    }

    public function updateHomepage(HomepageRequest $request, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $setting = $business->websiteSetting()->firstOrCreate([]);
        $data = $request->validated();
        unset($data['hero_image']);
        $data['hero_image_path'] = $images->replace($request->file('hero_image'), $setting->hero_image_path, "businesses/{$business->id}/brand");
        $setting->update($data);

        return back()->with('success', 'Homepage content updated.');
    }

    public function editAppearance(Request $request): View
    {
        $business = $request->user()->business;
        $setting = $business->websiteSetting()->firstOrCreate([]);

        return view('admin.website.appearance', compact('business', 'setting'));
    }

    public function updateAppearance(AppearanceRequest $request, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $setting = $business->websiteSetting()->firstOrCreate([]);
        $setting->update($request->safe()->only(['primary_color', 'accent_color', 'button_style']));
        $business->update([
            'logo_path' => $images->replace($request->file('logo'), $business->logo_path, "businesses/{$business->id}/brand"),
        ]);

        return back()->with('success', 'Website appearance updated.');
    }
}

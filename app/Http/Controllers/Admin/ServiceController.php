<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Services\ImageStorageService;
use App\Services\SlugService;
use App\Services\StaffingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Service::class);
        $services = $request->user()->business->services()
            ->with(['category', 'workers'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', compact('services'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Service::class);
        $business = $request->user()->business;
        $categories = $business->categories()->orderBy('sort_order')->orderBy('name')->get();
        $workers = $business->workers()->orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.services.create', compact('categories', 'workers'));
    }

    public function store(
        ServiceRequest $request,
        SlugService $slugs,
        ImageStorageService $images,
        StaffingService $staffing
    ): RedirectResponse {
        $business = $request->user()->business;
        $data = $request->validated();
        $workerIds = $data['qualified_worker_ids'] ?? [];
        unset($data['image'], $data['qualified_worker_ids']);
        $data['business_id'] = $business->id;
        $data['slug'] = $slugs->forBusiness(Service::class, $business, $data['name']);
        $data['image_path'] = $images->replace($request->file('image'), null, "businesses/{$business->id}/services");

        DB::transaction(function () use ($business, $data, $workerIds) {
            $service = $business->services()->create($data);
            $service->workers()->sync($workerIds);
        });

        $staffing->reconcileFutureBusiness($business);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Request $request, int $service): View
    {
        $business = $request->user()->business;
        $service = $business->services()->with('workers')->findOrFail($service);
        Gate::authorize('update', $service);
        $categories = $business->categories()->orderBy('sort_order')->orderBy('name')->get();
        $workers = $business->workers()->orderByDesc('is_active')->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'categories', 'workers'));
    }

    public function update(
        ServiceRequest $request,
        int $service,
        SlugService $slugs,
        ImageStorageService $images,
        StaffingService $staffing
    ): RedirectResponse {
        $business = $request->user()->business;
        $service = $business->services()->findOrFail($service);
        Gate::authorize('update', $service);
        $data = $request->validated();
        $workerIds = $data['qualified_worker_ids'] ?? [];
        unset($data['image'], $data['qualified_worker_ids']);
        $data['slug'] = $slugs->forBusiness(Service::class, $business, $data['name'], $service->id);
        $data['image_path'] = $images->replace($request->file('image'), $service->image_path, "businesses/{$business->id}/services");

        DB::transaction(function () use ($business, $service, $data, $workerIds, $staffing) {
            \App\Models\Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();
            $service->update($data);
            $service->workers()->sync($workerIds);
            $staffing->reconcileFutureBusiness($business);
        }, 3);

        return redirect()->route('admin.services.index')->with('success', 'Service updated. Staffing was recalculated.');
    }

    public function destroy(Request $request, int $service, ImageStorageService $images): RedirectResponse
    {
        $service = $request->user()->business->services()->findOrFail($service);
        Gate::authorize('delete', $service);
        $images->delete($service->image_path);
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted. Existing appointments keep their service record.');
    }
}

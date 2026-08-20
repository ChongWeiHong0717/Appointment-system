<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Services\ImageStorageService;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Service::class);
        $services = $request->user()->business->services()
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', compact('services'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Service::class);
        $categories = $request->user()->business->categories()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.services.create', compact('categories'));
    }

    public function store(ServiceRequest $request, SlugService $slugs, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();
        unset($data['image']);
        $data['business_id'] = $business->id;
        $data['slug'] = $slugs->forBusiness(Service::class, $business, $data['name']);
        $data['image_path'] = $images->replace($request->file('image'), null, "businesses/{$business->id}/services");
        $business->services()->create($data);

        return redirect()->route('admin.services.index')->with('success', 'Service created.');
    }

    public function edit(Request $request, int $service): View
    {
        $service = $request->user()->business->services()->findOrFail($service);
        Gate::authorize('update', $service);
        $categories = $request->user()->business->categories()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    public function update(ServiceRequest $request, int $service, SlugService $slugs, ImageStorageService $images): RedirectResponse
    {
        $business = $request->user()->business;
        $service = $business->services()->findOrFail($service);
        Gate::authorize('update', $service);
        $data = $request->validated();
        unset($data['image']);
        $data['slug'] = $slugs->forBusiness(Service::class, $business, $data['name'], $service->id);
        $data['image_path'] = $images->replace($request->file('image'), $service->image_path, "businesses/{$business->id}/services");
        $service->update($data);

        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
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

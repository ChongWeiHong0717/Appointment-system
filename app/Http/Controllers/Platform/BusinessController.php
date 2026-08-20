<?php

namespace App\Http\Controllers\Platform;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreBusinessRequest;
use App\Models\Business;
use App\Services\ImageStorageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,suspended'],
        ]);

        $search = trim($validated['search'] ?? '');
        $status = $validated['status'] ?? null;
        $businesses = Business::query()
            ->withCount([
                'appointments',
                'users as business_admins_count' => fn (Builder $query) => $query->where('role', UserRole::BusinessAdmin->value),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status, fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('platform.businesses.index', compact('businesses', 'search', 'status'));
    }

    public function create(): View
    {
        return view('platform.businesses.create', [
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = DB::transaction(function () use ($request): Business {
            $business = Business::query()->create([
                ...$request->validated(),
                'is_active' => true,
            ]);

            $business->websiteSetting()->create([
                'hero_heading' => "Welcome to {$business->name}",
                'hero_subtitle' => 'Choose a service and book a time that works for you.',
                'meta_title' => $business->name,
            ]);

            foreach (range(0, 6) as $day) {
                $closed = in_array($day, [0], true);
                $business->businessHours()->create([
                    'day_of_week' => $day,
                    'period_index' => 0,
                    'is_closed' => $closed,
                    'opens_at' => $closed ? null : '09:00',
                    'closes_at' => $closed ? null : '17:00',
                ]);
            }

            return $business;
        });

        return redirect()->route('platform.businesses.show', $business)
            ->with('success', 'Business created. Add at least one business administrator before hand-off.');
    }

    public function show(Business $business): View
    {
        $business->loadCount(['appointments', 'categories', 'services']);
        $admins = $business->users()
            ->where('role', UserRole::BusinessAdmin->value)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('platform.businesses.show', compact('business', 'admins'));
    }

    public function updateStatus(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        $business->update(['is_active' => (bool) $validated['is_active']]);

        return back()->with('success', $business->is_active ? 'Business reactivated.' : 'Business suspended.');
    }

    public function destroy(Business $business, ImageStorageService $images): RedirectResponse
    {
        if ($business->appointments()->exists()) {
            $business->update(['is_active' => false]);

            return back()->with('warning', 'This business has appointment history, so it was suspended instead of permanently deleted.');
        }

        $imagePaths = collect([$business->logo_path, $business->websiteSetting?->hero_image_path])
            ->merge($business->categories()->withTrashed()->pluck('image_path'))
            ->merge($business->services()->withTrashed()->pluck('image_path'))
            ->filter()
            ->unique();

        DB::transaction(function () use ($business): void {
            DB::table('sessions')->whereIn('user_id', $business->users()->pluck('id'))->delete();
            $business->users()->delete();
            $business->specialDates()->delete();
            $business->businessHours()->delete();
            $business->services()->withTrashed()->forceDelete();
            $business->categories()->withTrashed()->forceDelete();
            $business->websiteSetting()->delete();
            $business->delete();
        });

        $imagePaths->each(fn (string $path) => $images->delete($path));

        return redirect()->route('platform.businesses.index')->with('success', 'Business permanently deleted.');
    }
}

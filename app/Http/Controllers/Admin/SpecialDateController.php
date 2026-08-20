<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpecialDateRequest;
use App\Models\SpecialDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SpecialDateController extends Controller
{
    public function store(SpecialDateRequest $request): RedirectResponse
    {
        Gate::authorize('create', SpecialDate::class);
        $request->user()->business->specialDates()->create($this->values($request));

        return back()->with('success', 'Special date added.');
    }

    public function update(SpecialDateRequest $request, int $specialDate): RedirectResponse
    {
        $specialDate = $request->user()->business->specialDates()->findOrFail($specialDate);
        Gate::authorize('update', $specialDate);
        $specialDate->update($this->values($request));

        return back()->with('success', 'Special date updated.');
    }

    public function destroy(Request $request, int $specialDate): RedirectResponse
    {
        $specialDate = $request->user()->business->specialDates()->findOrFail($specialDate);
        Gate::authorize('delete', $specialDate);
        $specialDate->delete();

        return back()->with('success', 'Special date removed.');
    }

    private function values(SpecialDateRequest $request): array
    {
        $data = $request->validated();
        if ($data['is_closed']) {
            $data['opens_at'] = null;
            $data['closes_at'] = null;
        }

        return $data;
    }
}

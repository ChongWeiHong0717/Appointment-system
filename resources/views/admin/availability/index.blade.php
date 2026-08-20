@extends('layouts.admin')
@section('title', 'Availability')
@section('header', 'Booking availability')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Availability" title="Hours and special dates" description="These rules control every time offered online and at the front desk." />
    <x-admin.form-errors />
    <div class="mt-8 grid gap-8 xl:grid-cols-[1.15fr_.85fr]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div><h2 class="text-lg font-black text-slate-950">Weekly business hours</h2><p class="mt-1 text-sm text-slate-500">Set one opening period per day for V1.</p></div>
            <form class="mt-7" action="{{ route('admin.availability.hours.update') }}" method="POST">@csrf @method('PUT')
                <div class="divide-y divide-slate-100">
                    @foreach([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'] as $day => $label)
                        @php($index = $loop->index)
                        @php($record = $hours->get($day))
                        @php($closed = (bool) old("hours.$index.is_closed", $record?->is_closed ?? true))
                        <div x-data="{ closed: {{ Js::from($closed) }} }" class="grid gap-4 py-5 sm:grid-cols-[140px_1fr] sm:items-center">
                            <div><p class="font-black text-slate-800">{{ $label }}</p><label class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-slate-500"><input type="hidden" name="hours[{{ $index }}][is_closed]" value="0"><input x-model="closed" class="rounded border-slate-300 text-emerald-700" name="hours[{{ $index }}][is_closed]" type="checkbox" value="1"> Closed</label><input type="hidden" name="hours[{{ $index }}][day_of_week]" value="{{ $day }}"></div>
                            <div class="grid grid-cols-2 gap-3" :class="closed ? 'opacity-40' : ''"><div><label class="mb-1 block text-xs font-bold text-slate-500">Opens</label><input :disabled="closed" class="form-input py-2.5" name="hours[{{ $index }}][opens_at]" type="time" value="{{ old("hours.$index.opens_at", $record?->opens_at ? substr($record->opens_at, 0, 5) : '09:00') }}"></div><div><label class="mb-1 block text-xs font-bold text-slate-500">Closes</label><input :disabled="closed" class="form-input py-2.5" name="hours[{{ $index }}][closes_at]" type="time" value="{{ old("hours.$index.closes_at", $record?->closes_at ? substr($record->closes_at, 0, 5) : '18:00') }}"></div></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-end border-t border-slate-100 pt-6"><button class="brand-button px-6 py-3" style="--brand:#047857" type="submit">Save weekly hours</button></div>
            </form>
        </section>

        <div class="space-y-8">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" x-data="{ closed: {{ Js::from((bool) old('is_closed', true)) }} }">
                <h2 class="text-lg font-black text-slate-950">Add special date</h2><p class="mt-1 text-sm text-slate-500">Close for a date or use different opening hours.</p>
                <form class="mt-6 space-y-5" action="{{ route('admin.availability.special-dates.store') }}" method="POST">@csrf
                    <div><label class="form-label" for="date">Date</label><input class="form-input" id="date" name="date" type="date" value="{{ old('date') }}" required></div>
                    <label class="flex items-center gap-3"><input type="hidden" name="is_closed" value="0"><input x-model="closed" class="h-5 w-5 rounded border-slate-300 text-emerald-700" name="is_closed" type="checkbox" value="1"><span class="text-sm font-black text-slate-800">Closed all day</span></label>
                    <div class="grid grid-cols-2 gap-3" :class="closed ? 'opacity-40' : ''"><div><label class="form-label" for="special_opens">Opens</label><input :disabled="closed" class="form-input" id="special_opens" name="opens_at" type="time" value="{{ old('opens_at', '10:00') }}"></div><div><label class="form-label" for="special_closes">Closes</label><input :disabled="closed" class="form-input" id="special_closes" name="closes_at" type="time" value="{{ old('closes_at', '14:00') }}"></div></div>
                    <div><label class="form-label" for="note">Note or reason</label><input class="form-input" id="note" name="note" value="{{ old('note') }}" maxlength="255" placeholder="Public holiday"></div>
                    <button class="brand-button w-full px-5 py-3" style="--brand:#047857" type="submit">Add special date</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5"><h2 class="text-lg font-black text-slate-950">Special dates</h2><p class="mt-1 text-sm text-slate-500">Specific dates take priority over weekly hours.</p></div>
                <div class="divide-y divide-slate-100">
                    @forelse($specialDates as $specialDate)
                        <details class="group px-6 py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4"><div><p class="font-black text-slate-900">{{ $specialDate->date->format('D, j M Y') }}</p><p class="mt-1 text-sm text-slate-500">{{ $specialDate->is_closed ? 'Closed' : Carbon\CarbonImmutable::parse($specialDate->opens_at)->format('g:i A').' – '.Carbon\CarbonImmutable::parse($specialDate->closes_at)->format('g:i A') }}{{ $specialDate->note ? ' · '.$specialDate->note : '' }}</p></div><span class="text-sm font-black text-emerald-700 group-open:hidden">Edit</span></summary>
                            <form x-data="{ closed: {{ Js::from($specialDate->is_closed) }} }" class="mt-5 space-y-4 rounded-2xl bg-slate-50 p-4" action="{{ route('admin.availability.special-dates.update', $specialDate) }}" method="POST">@csrf @method('PUT')<div><label class="form-label">Date</label><input class="form-input" name="date" type="date" value="{{ $specialDate->date->toDateString() }}" required></div><label class="flex items-center gap-3"><input type="hidden" name="is_closed" value="0"><input x-model="closed" class="h-5 w-5 rounded border-slate-300 text-emerald-700" name="is_closed" type="checkbox" value="1"><span class="text-sm font-black">Closed all day</span></label><div class="grid grid-cols-2 gap-3" :class="closed ? 'opacity-40' : ''"><input :disabled="closed" class="form-input" name="opens_at" type="time" value="{{ $specialDate->opens_at ? substr($specialDate->opens_at, 0, 5) : '10:00' }}"><input :disabled="closed" class="form-input" name="closes_at" type="time" value="{{ $specialDate->closes_at ? substr($specialDate->closes_at, 0, 5) : '14:00' }}"></div><input class="form-input" name="note" value="{{ $specialDate->note }}" maxlength="255"><button class="brand-button w-full px-4 py-2.5" style="--brand:#047857" type="submit">Save changes</button></form>
                            <form class="mt-2" action="{{ route('admin.availability.special-dates.destroy', $specialDate) }}" method="POST" onsubmit="return confirm('Remove this special date?')">@csrf @method('DELETE')<button class="w-full rounded-xl px-4 py-2.5 text-sm font-black text-rose-600 hover:bg-rose-50" type="submit">Remove date</button></form>
                        </details>
                    @empty
                        <div class="px-6 py-10 text-center text-sm text-slate-500">No special dates configured.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

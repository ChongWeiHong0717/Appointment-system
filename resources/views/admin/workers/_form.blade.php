<x-admin.form-errors />
@php
    $selectedServices = collect(old(
        'service_ids',
        isset($worker) ? $worker->services->pluck('id')->all() : $services->pluck('id')->all()
    ))->map(fn ($id) => (int) $id);
@endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div><label class="form-label" for="name">Worker name</label><input class="form-input" id="name" name="name" value="{{ old('name', $worker->name ?? '') }}" required maxlength="120"></div>
    <div><label class="form-label" for="phone">Phone</label><input class="form-input" id="phone" name="phone" value="{{ old('phone', $worker->phone ?? '') }}" maxlength="40" placeholder="Optional"></div>
    <div class="sm:col-span-2"><label class="form-label" for="email">Email</label><input class="form-input" id="email" name="email" type="email" value="{{ old('email', $worker->email ?? '') }}" maxlength="255" placeholder="Optional"></div>
    <label class="flex items-center gap-3 sm:col-span-2"><input type="hidden" name="is_active" value="0"><input class="h-5 w-5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" name="is_active" type="checkbox" value="1" @checked(old('is_active', $worker->is_active ?? true))><span><span class="block text-sm font-black text-slate-800">Active worker</span><span class="block text-xs text-slate-500">Active workers can be assigned to appointments. Turn this off for staff who have left or are unavailable indefinitely.</span></span></label>

    <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <p class="text-sm font-black text-slate-900">Services this worker can perform</p>
        <p class="mt-1 text-xs text-slate-500">A worker must be qualified for a service before Bookwise can assign them.</p>
        @if($services->isEmpty())
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">There are no services yet. You can still save the worker and assign services later.</div>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($services as $service)
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                        <input class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked($selectedServices->contains($service->id))>
                        <span><span class="block text-sm font-bold text-slate-800">{{ $service->name }}</span><span class="mt-0.5 block text-xs text-slate-400">{{ $service->category->name }}</span></span>
                    </label>
                @endforeach
            </div>
        @endif
    </div>
</div>
<div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6"><a class="rounded-xl px-5 py-3 text-sm font-black text-slate-500 hover:bg-slate-50" href="{{ route('admin.workers.index') }}">Cancel</a><button class="brand-button px-6 py-3" style="--brand:#047857" type="submit">{{ $submitLabel }}</button></div>

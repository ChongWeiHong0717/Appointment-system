<x-admin.form-errors />
@php
    $selectedWorkers = collect(old(
        'qualified_worker_ids',
        isset($service) ? $service->workers->pluck('id')->all() : $workers->where('is_active', true)->pluck('id')->all()
    ))->map(fn ($id) => (int) $id);
@endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div><label class="form-label" for="category_id">Category</label><select class="form-input" id="category_id" name="category_id" required><option value="">Choose category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int) old('category_id', $service->category_id ?? 0) === $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (hidden)' }}</option>@endforeach</select></div>
    <div><label class="form-label" for="name">Service name</label><input class="form-input" id="name" name="name" value="{{ old('name', $service->name ?? '') }}" required maxlength="120"></div>
    <div class="sm:col-span-2"><label class="form-label" for="description">Description</label><textarea class="form-input min-h-28" id="description" name="description" maxlength="3000">{{ old('description', $service->description ?? '') }}</textarea></div>
    <div><label class="form-label" for="price">Price (RM)</label><input class="form-input" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $service->price ?? '') }}" placeholder="Leave blank for price on request"></div>
    <div><label class="form-label" for="duration_minutes">Duration (minutes)</label><input class="form-input" id="duration_minutes" name="duration_minutes" type="number" step="5" min="5" max="1440" value="{{ old('duration_minutes', $service->duration_minutes ?? 60) }}" required></div>
    <div><label class="form-label" for="workers_required">Workers required</label><input class="form-input" id="workers_required" name="workers_required" type="number" min="1" max="50" value="{{ old('workers_required', $service->workers_required ?? 1) }}" required><p class="mt-2 text-xs text-slate-400">How many qualified workers this appointment occupies at the same time.</p></div>
    <div><label class="form-label" for="sort_order">Display order</label><input class="form-input" id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $service->sort_order ?? 0) }}" required></div>
    <div><label class="form-label" for="image">Service image</label><input class="form-input file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-bold" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"><p class="mt-2 text-xs text-slate-400">JPG, PNG or WebP, up to 4 MB.</p></div>
    <label class="flex items-center gap-3 sm:col-span-2"><input type="hidden" name="is_active" value="0"><input class="h-5 w-5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" name="is_active" type="checkbox" value="1" @checked(old('is_active', $service->is_active ?? true))><span><span class="block text-sm font-black text-slate-800">Bookable and visible</span><span class="block text-xs text-slate-500">Show this service and allow new bookings.</span></span></label>

    <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-sm font-black text-slate-900">Qualified workers</p><p class="mt-1 text-xs text-slate-500">Only selected workers can be assigned to this service.</p></div><a class="text-xs font-black text-emerald-700" href="{{ route('admin.workers.index') }}">Manage workers →</a></div>
        @if($workers->isEmpty())
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">No workers are configured yet. Bookwise will keep using legacy one-appointment-at-a-time availability until an active worker is added.</div>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($workers as $worker)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3"><input class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" type="checkbox" name="qualified_worker_ids[]" value="{{ $worker->id }}" @checked($selectedWorkers->contains($worker->id))><span class="text-sm font-bold text-slate-700">{{ $worker->name }}{{ $worker->is_active ? '' : ' (inactive)' }}</span></label>
                @endforeach
            </div>
        @endif
    </div>
</div>
<div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6"><a class="rounded-xl px-5 py-3 text-sm font-black text-slate-500 hover:bg-slate-50" href="{{ route('admin.services.index') }}">Cancel</a><button class="brand-button px-6 py-3" style="--brand:#047857" type="submit">{{ $submitLabel }}</button></div>

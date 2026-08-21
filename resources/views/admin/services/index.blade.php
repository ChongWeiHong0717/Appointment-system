@extends('layouts.admin')
@section('title', 'Services')
@section('header', 'Service catalog')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Catalog" title="Services" description="Control pricing, timing, worker requirements, visibility, and ordering."><x-slot:actions><a class="brand-button px-5 py-3" style="--brand:#047857" href="{{ route('admin.services.create') }}">+ New service</a></x-slot:actions></x-admin.page-header>
    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @forelse($services as $service)
            <div class="grid gap-4 border-b border-slate-100 px-6 py-5 last:border-0 xl:grid-cols-[1fr_145px_170px_120px_auto] xl:items-center xl:px-8">
                <div class="flex min-w-0 items-center gap-4">@if($service->image_path)<img class="h-14 w-14 shrink-0 rounded-2xl object-cover" src="{{ Storage::url($service->image_path) }}" alt="">@else<span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-violet-50 font-black text-violet-700">{{ Str::upper(Str::substr($service->name, 0, 1)) }}</span>@endif<div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="truncate font-black text-slate-900">{{ $service->name }}</p><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $service->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $service->is_active ? 'Active' : 'Hidden' }}</span></div><p class="mt-1 text-sm text-slate-500">{{ $service->category->name }}</p></div></div>
                <p class="text-sm font-bold text-slate-600">{{ $service->duration_minutes }} minutes</p>
                <div><p class="text-sm font-black text-slate-800">{{ $service->workers_required }} {{ Str::plural('worker', $service->workers_required) }}</p><p class="mt-1 text-xs text-slate-400">{{ $service->workers->count() }} qualified</p></div>
                <p class="text-sm font-black text-slate-900">{{ $service->price === null ? 'On request' : 'RM'.number_format((float) $service->price, 2) }}</p>
                <div class="flex items-center gap-2"><a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('admin.services.edit', $service) }}">Edit</a><form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete this service? Existing appointments will keep their historical details.')">@csrf @method('DELETE')<button class="rounded-xl px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button></form></div>
            </div>
        @empty
            <div class="px-6 py-16 text-center"><p class="font-black text-slate-900">No services yet</p><p class="mt-2 text-sm text-slate-500">Create categories first, then add bookable services.</p><a class="brand-button mt-6 px-5 py-3" style="--brand:#047857" href="{{ route('admin.services.create') }}">Create service</a></div>
        @endforelse
    </div>
</div>
@endsection

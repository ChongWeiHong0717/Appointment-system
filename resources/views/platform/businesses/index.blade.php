@extends('layouts.platform')
@section('title', 'Businesses')
@section('header', 'Business directory')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Platform" title="Businesses" description="Provision tenants, control access, and open their customer or administrator entry points.">
        <x-slot:actions><a class="brand-button px-5 py-3" style="--brand:#047857" href="{{ route('platform.businesses.create') }}">+ New business</a></x-slot:actions>
    </x-admin.page-header>
    <x-admin.form-errors />

    <form class="mt-8 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_180px_auto]" method="GET">
        <input class="form-input" name="search" value="{{ $search }}" placeholder="Search name, slug, or email">
        <select class="form-input" name="status">
            <option value="">All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="suspended" @selected($status === 'suspended')>Suspended</option>
        </select>
        <button class="brand-button px-5 py-3" style="--brand:#047857" type="submit">Filter</button>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @forelse($businesses as $business)
            <div class="grid gap-5 border-b border-slate-100 px-6 py-6 last:border-0 lg:grid-cols-[1fr_auto] lg:items-center sm:px-8">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-50 text-lg font-black text-emerald-700">{{ Str::upper(Str::substr($business->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2"><a class="font-black text-slate-950 hover:text-emerald-700" href="{{ route('platform.businesses.show', $business) }}">{{ $business->name }}</a><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $business->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $business->is_active ? 'Active' : 'Suspended' }}</span></div>
                        <p class="mt-1 truncate text-sm text-slate-500">/{{ $business->slug }} · {{ $business->business_admins_count }} {{ Str::plural('admin', $business->business_admins_count) }} · {{ $business->appointments_count }} {{ Str::plural('appointment', $business->appointments_count) }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('public.home', $business) }}" target="_blank">Public site ↗</a>
                    <a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('login', ['business' => $business->slug]) }}" target="_blank">Admin login ↗</a>
                    <a class="brand-button px-4 py-2 text-sm" style="--brand:#047857" href="{{ route('platform.businesses.show', $business) }}">Manage</a>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center"><p class="font-black text-slate-900">No businesses found</p><p class="mt-2 text-sm text-slate-500">Create the first business or change the filters.</p></div>
        @endforelse
    </div>

    @if($businesses->hasPages())<div class="mt-6">{{ $businesses->links() }}</div>@endif
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Today at a glance')

@section('content')
    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-sm font-black uppercase tracking-[.2em] text-emerald-700">Dashboard</p><h1 class="mt-2 text-3xl font-black tracking-[-.035em] text-slate-950 sm:text-4xl">Good {{ now($business->timezone)->hour < 12 ? 'morning' : (now($business->timezone)->hour < 18 ? 'afternoon' : 'evening') }}.</h1><p class="mt-2 text-slate-500">Here’s what’s happening at {{ $business->name }} today.</p></div>
            @if(Route::has('admin.appointments.create'))<a class="brand-button px-5 py-3" style="--brand: #047857" href="{{ route('admin.appointments.create') }}">+ New appointment</a>@endif
        </div>

        <div class="mt-9 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([['Today’s appointments', $summary['today'], 'bg-sky-50 text-sky-700'], ['Upcoming', $summary['upcoming'], 'bg-violet-50 text-violet-700'], ['Checked in', $summary['checked_in'], 'bg-amber-50 text-amber-700'], ['Completed', $summary['completed'], 'bg-emerald-50 text-emerald-700']] as [$label, $value, $tone])
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $tone }}">{{ $label }}</span><p class="mt-6 text-4xl font-black tracking-tight text-slate-950">{{ $value }}</p></div>
            @endforeach
        </div>

        @if($staffingSummary['enabled'])
            <section class="mt-6 rounded-3xl border {{ $staffingSummary['conflicts'] ? 'border-rose-200 bg-rose-50' : 'border-slate-200 bg-white' }} p-6 shadow-sm sm:p-7">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"><div><p class="text-xs font-black uppercase tracking-[.18em] {{ $staffingSummary['conflicts'] ? 'text-rose-700' : 'text-emerald-700' }}">Staffing</p><h2 class="mt-2 text-xl font-black text-slate-950">{{ $staffingSummary['conflicts'] ? $staffingSummary['conflicts'].' appointment '.Str::plural('conflict', $staffingSummary['conflicts']) : 'Today is fully staffed' }}</h2><p class="mt-1 text-sm text-slate-500">{{ $staffingSummary['present'] }} present · {{ $staffingSummary['absent'] }} absent · {{ $staffingSummary['active'] }} active workers</p></div><a class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('admin.workers.index', ['date' => $today]) }}">Manage staffing →</a></div>
            </section>
        @else
            <section class="mt-6 rounded-3xl border border-violet-200 bg-violet-50 p-6 shadow-sm sm:p-7"><div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"><div><p class="text-xs font-black uppercase tracking-[.18em] text-violet-700">Parallel booking upgrade</p><h2 class="mt-2 text-xl font-black text-slate-950">Set up workers to unlock capacity-aware bookings</h2><p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Until you add an active worker, Bookwise keeps the previous one-appointment-at-a-time availability behaviour.</p></div><a class="brand-button px-5 py-3" style="--brand:#7c3aed" href="{{ route('admin.workers.create') }}">Add first worker</a></div></section>
        @endif

        <section class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 sm:px-8"><div><h2 class="text-lg font-black text-slate-950">Today’s schedule</h2><p class="mt-1 text-sm text-slate-500">{{ Carbon\CarbonImmutable::parse($today)->format('l, j F Y') }}</p></div>@if(Route::has('admin.appointments.index'))<a class="text-sm font-black text-emerald-700" href="{{ route('admin.appointments.index', ['scope' => 'today']) }}">View all →</a>@endif</div>
            <div class="divide-y divide-slate-100">
                @forelse($appointments as $appointment)
                    @php($staffStatus = $staffingStatuses[$appointment->id] ?? null)
                    <a class="grid gap-4 px-6 py-5 transition hover:bg-slate-50 sm:grid-cols-[100px_1fr_auto] sm:items-center sm:px-8" href="{{ Route::has('admin.appointments.show') ? route('admin.appointments.show', $appointment) : '#' }}">
                        <p class="text-lg font-black text-slate-950">{{ Carbon\CarbonImmutable::parse($appointment->start_time)->format('g:i A') }}</p>
                        <div><div class="flex flex-wrap items-center gap-2"><p class="font-black text-slate-900">{{ $appointment->customer_name }}</p>@if($staffStatus && $staffStatus['managed'] && ! $staffStatus['healthy'])<span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-black text-rose-700">⚠ Missing {{ $staffStatus['missing'] }} worker</span>@endif</div><p class="mt-1 text-sm text-slate-500">{{ $appointment->service->name }} · {{ $appointment->customer_phone }}@if($staffStatus && $staffStatus['managed'] && $staffStatus['healthy']) · {{ $staffStatus['assigned'] }}/{{ $staffStatus['required'] }} workers @endif</p></div>
                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-black ring-1 ring-inset {{ $appointment->status->badgeClasses() }}">{{ $appointment->status->label() }}</span>
                    </a>
                @empty
                    <div class="px-6 py-16 text-center"><span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl">☀</span><p class="mt-5 font-black text-slate-900">A clear schedule today</p><p class="mt-2 text-sm text-slate-500">New appointments will appear here as they are booked.</p></div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

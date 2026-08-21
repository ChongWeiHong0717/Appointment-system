@extends('layouts.admin')
@section('title', 'Workers')
@section('header', 'Workers & staffing')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Staff capacity" title="Workers" description="Manage worker qualifications, absences and the capacity that powers appointment availability.">
        <x-slot:actions><a class="brand-button px-5 py-3" style="--brand:#047857" href="{{ route('admin.workers.create') }}">+ Add worker</a></x-slot:actions>
    </x-admin.page-header>

    <div class="mt-7 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-black text-slate-900">Staffing date</p><p class="mt-1 text-xs text-slate-500">Absence status and conflicts below are calculated for this day.</p></div>
        <form class="flex gap-2" method="GET" action="{{ route('admin.workers.index') }}"><input class="form-input" type="date" name="date" value="{{ $selectedDate }}"><button class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" type="submit">View date</button></form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Active workers', $summary['active'], 'bg-sky-50 text-sky-700'], ['Available', $summary['available'], 'bg-emerald-50 text-emerald-700'], ['Absent', $summary['absent'], 'bg-amber-50 text-amber-700'], ['Staffing conflicts', $summary['conflicts'], $summary['conflicts'] ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600']] as [$label, $value, $tone])
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $tone }}">{{ $label }}</span><p class="mt-5 text-3xl font-black text-slate-950">{{ $value }}</p></div>
        @endforeach
    </div>

    @if($conflicts->isNotEmpty())
        <section class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-6 shadow-sm">
            <div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-lg">!</span><div><h2 class="font-black text-rose-900">Appointments need staffing attention</h2><p class="mt-1 text-sm text-rose-700">These bookings remain confirmed. Bookwise could not find enough qualified workers after the latest staffing changes.</p></div></div>
            <div class="mt-5 space-y-2">
                @foreach($appointments as $appointment)
                    @if($conflicts->has($appointment->id))
                        @php($status = $conflicts[$appointment->id])
                        <a class="flex flex-col gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-3 transition hover:border-rose-300 sm:flex-row sm:items-center sm:justify-between" href="{{ route('admin.appointments.show', $appointment) }}"><div><p class="font-black text-slate-900">{{ Carbon\CarbonImmutable::parse($appointment->start_time)->format('g:i A') }} · {{ $appointment->customer_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $appointment->service->name }}</p></div><span class="text-sm font-black text-rose-700">Missing {{ $status['missing'] }} {{ Str::plural('worker', $status['missing']) }} →</span></a>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @forelse($workers as $worker)
            @php($absence = $worker->absences->first())
            <div class="border-b border-slate-100 p-6 last:border-0 lg:p-8">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="text-lg font-black text-slate-950">{{ $worker->name }}</h2><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $worker->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $worker->is_active ? 'Active' : 'Inactive' }}</span>@if($absence)<span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-black text-amber-700">Absent {{ $selectedDate }}</span>@endif</div>
                        <p class="mt-2 text-sm text-slate-500">{{ $worker->email ?: 'No email' }}{{ $worker->phone ? ' · '.$worker->phone : '' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">@forelse($worker->services as $service)<span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">{{ $service->name }}</span>@empty<span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">No service qualifications</span>@endforelse</div>
                    </div>
                    <div class="w-full xl:w-[390px]">
                        @if($worker->is_active)
                            @if($absence)
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-black uppercase tracking-[.15em] text-amber-700">Absent</p><p class="mt-1 text-sm text-amber-900">{{ $absence->reason ?: 'No reason recorded.' }}</p><form class="mt-3" action="{{ route('admin.workers.absences.destroy', [$worker, $absence]) }}" method="POST">@csrf @method('DELETE')<button class="rounded-xl bg-white px-4 py-2 text-sm font-black text-amber-800 ring-1 ring-amber-200 hover:bg-amber-100" type="submit">Restore worker</button></form></div>
                            @else
                                <form class="rounded-2xl border border-slate-200 bg-slate-50 p-4" action="{{ route('admin.workers.absences.store', $worker) }}" method="POST">@csrf<input type="hidden" name="date" value="{{ $selectedDate }}"><label class="text-xs font-black uppercase tracking-[.15em] text-slate-500" for="reason-{{ $worker->id }}">Mark absent on {{ $selectedDate }}</label><div class="mt-2 flex gap-2"><input class="form-input" id="reason-{{ $worker->id }}" name="reason" maxlength="255" placeholder="Reason (optional)"><button class="shrink-0 rounded-xl bg-amber-100 px-4 py-2 text-sm font-black text-amber-800 hover:bg-amber-200" type="submit" onclick="return confirm('Mark this worker absent? Bookwise will try to reassign affected appointments.')">Mark absent</button></div></form>
                            @endif
                        @endif
                        <div class="mt-3 flex justify-end gap-2"><a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('admin.workers.edit', $worker) }}">Edit</a><form action="{{ route('admin.workers.destroy', $worker) }}" method="POST" onsubmit="return confirm('Remove this worker? If they have appointment history, Bookwise will deactivate them instead.')">@csrf @method('DELETE')<button class="rounded-xl px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Remove</button></form></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center"><span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl">👥</span><p class="mt-5 font-black text-slate-900">No workers configured yet</p><p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">Bookwise is currently using its legacy one-appointment-at-a-time availability. Add workers to unlock parallel bookings, qualifications and absence handling.</p><a class="brand-button mt-6 px-5 py-3" style="--brand:#047857" href="{{ route('admin.workers.create') }}">Add first worker</a></div>
        @endforelse
    </div>
</div>
@endsection

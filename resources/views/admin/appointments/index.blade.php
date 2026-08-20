@extends('layouts.admin')
@section('title', 'Appointments')
@section('header', 'Appointment management')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Appointments" title="Schedule" description="Find, filter, and manage every customer appointment."><x-slot:actions><a class="brand-button px-5 py-3" style="--brand:#047857" href="{{ route('admin.appointments.create') }}">+ New appointment</a></x-slot:actions></x-admin.page-header>
    <x-admin.form-errors />

    <div class="mt-8 flex gap-2 overflow-x-auto pb-1">@foreach(['today' => 'Today', 'upcoming' => 'Upcoming', 'all' => 'All appointments'] as $value => $label)<a class="whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-black {{ $scope === $value ? 'bg-slate-950 text-white' : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}" href="{{ route('admin.appointments.index', ['scope' => $value]) }}">{{ $label }}</a>@endforeach</div>

    <form class="mt-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[1fr_170px_170px_180px_auto]" method="GET" action="{{ route('admin.appointments.index') }}"><input type="hidden" name="scope" value="{{ $scope }}"><input class="form-input py-2.5" name="q" value="{{ request('q') }}" placeholder="Customer name or phone"><input class="form-input py-2.5" name="date" type="date" value="{{ request('date') }}"><select class="form-input py-2.5" name="status"><option value="">All statuses</option>@foreach(App\Enums\AppointmentStatus::cases() as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select><select class="form-input py-2.5" name="service_id"><option value="">All services</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected((int) request('service_id') === $service->id)>{{ $service->name }}</option>@endforeach</select><div class="flex gap-2"><button class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-black text-white" type="submit">Filter</button><a class="rounded-xl px-3 py-2.5 text-sm font-black text-slate-500 hover:bg-slate-50" href="{{ route('admin.appointments.index', ['scope' => $scope]) }}">Clear</a></div></form>

    <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @forelse($appointments as $appointment)
            <a class="grid gap-4 border-b border-slate-100 px-6 py-5 transition last:border-0 hover:bg-slate-50 lg:grid-cols-[150px_1fr_1fr_auto] lg:items-center lg:px-8" href="{{ route('admin.appointments.show', $appointment) }}"><div><p class="font-black text-slate-950">{{ $appointment->appointment_date->format('D, j M') }}</p><p class="mt-1 text-sm font-bold text-slate-500">{{ Carbon\CarbonImmutable::parse($appointment->start_time)->format('g:i A') }}</p></div><div><p class="font-black text-slate-900">{{ $appointment->customer_name }}</p><p class="mt-1 text-sm text-slate-500">{{ $appointment->customer_phone }}</p></div><div><p class="font-bold text-slate-800">{{ $appointment->service->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $appointment->service->category->name }} · {{ $appointment->service->duration_minutes }} min</p></div><x-admin.status-badge :status="$appointment->status" /></a>
        @empty
            <div class="px-6 py-16 text-center"><p class="font-black text-slate-900">No matching appointments</p><p class="mt-2 text-sm text-slate-500">Try clearing a filter or create a manual appointment.</p></div>
        @endforelse
    </div>
    @if($appointments->hasPages())<div class="mt-6">{{ $appointments->links() }}</div>@endif
</div>
@endsection

@extends('layouts.public')

@section('title', 'Appointment confirmed — '.$business->name)

@section('content')
<section class="relative overflow-hidden py-16 sm:py-24">
    <div class="absolute inset-x-0 top-0 -z-10 h-96 opacity-40" style="background: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--brand) 30%, white), transparent 68%)"></div>
    <div class="mx-auto max-w-3xl px-5 lg:px-8">
        <div class="text-center">
            <span class="mx-auto grid h-20 w-20 place-items-center rounded-full text-3xl text-white shadow-xl" style="background: var(--brand)">✓</span>
            <p class="mt-7 text-sm font-black uppercase tracking-[.2em]" style="color: var(--brand)">Appointment confirmed</p>
            <h1 class="mt-3 text-4xl font-black tracking-[-.04em] text-slate-950 sm:text-5xl">You’re all set, {{ Str::before($appointment->customer_name, ' ') }}.</h1>
            <p class="mx-auto mt-5 max-w-xl text-lg leading-8 text-slate-600">Your appointment has been reserved with {{ $business->name }}. Keep this page for your records.</p>
        </div>
        <div class="soft-card mt-10 overflow-hidden">
            <div class="p-7 sm:p-10">
                <div class="flex flex-col gap-4 border-b border-slate-100 pb-7 sm:flex-row sm:items-start sm:justify-between">
                    <div><p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Service</p><h2 class="mt-2 text-2xl font-black text-slate-950">{{ $appointment->service->name }}</h2><p class="mt-2 text-sm text-slate-500">{{ $appointment->service->category->name }} · {{ $appointment->service->duration_minutes }} minutes</p></div>
                    @if($appointment->service->price !== null)<p class="text-lg font-black" style="color: var(--brand)">RM{{ number_format((float) $appointment->service->price, 2) }}</p>@endif
                </div>
                <dl class="mt-7 grid gap-7 sm:grid-cols-2">
                    <div><dt class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Date</dt><dd class="mt-2 font-black text-slate-900">{{ $appointment->appointment_date->format('l, j F Y') }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Time</dt><dd class="mt-2 font-black text-slate-900">{{ Carbon\CarbonImmutable::parse($appointment->start_time)->format('g:i A') }} – {{ Carbon\CarbonImmutable::parse($appointment->end_time)->format('g:i A') }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Name</dt><dd class="mt-2 font-black text-slate-900">{{ $appointment->customer_name }}</dd></div>
                    <div><dt class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Phone</dt><dd class="mt-2 font-black text-slate-900">{{ $appointment->customer_phone }}</dd></div>
                </dl>
                @if($appointment->customer_notes)<div class="mt-7 rounded-2xl bg-slate-50 p-5"><p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">Your note</p><p class="mt-2 text-sm leading-6 text-slate-700">{{ $appointment->customer_notes }}</p></div>@endif
            </div>
            <div class="border-t border-slate-100 bg-slate-50 px-7 py-6 text-sm text-slate-600 sm:px-10">Need to make a change? Contact {{ $business->name }}@if($business->phone) at <a class="font-black hover:underline" style="color: var(--brand)" href="tel:{{ App\Support\PhoneNumber::normalize($business->phone) }}">{{ $business->phone }}</a>@endif.</div>
        </div>
        <div class="mt-8 flex justify-center"><a class="brand-button px-7 py-4" href="{{ route('public.home', $business) }}">Return to website</a></div>
    </div>
</section>
@endsection

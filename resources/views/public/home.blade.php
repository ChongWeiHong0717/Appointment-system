@extends('layouts.public')

@section('title', $business->name)

@section('content')
@php
    $today = now($business->timezone);
    $todayOpening = $hours->get($today->dayOfWeek);

    $isOpenToday = $todayOpening && ! $todayOpening->is_closed;
    $todayHours = $isOpenToday
        ? Carbon\CarbonImmutable::parse($todayOpening->opens_at)->format('g:i A').' – '.Carbon\CarbonImmutable::parse($todayOpening->closes_at)->format('g:i A')
        : 'Closed today';

    $serviceCategories = $categories->take(4);
@endphp

<section class="relative isolate min-h-[calc(100vh-5rem)] overflow-hidden bg-[#f8fafc]">
    {{-- Soft brand atmosphere --}}
    <div class="pointer-events-none absolute inset-0 -z-20">
        <div class="absolute -left-32 top-20 h-80 w-80 rounded-full opacity-15 blur-3xl" style="background: var(--brand)"></div>
        <div class="absolute -right-28 bottom-0 h-96 w-96 rounded-full opacity-15 blur-3xl" style="background: var(--accent)"></div>
    </div>

    <div class="mx-auto grid min-h-[calc(100vh-5rem)] max-w-7xl items-center gap-12 px-5 py-12 lg:grid-cols-[1.02fr_.98fr] lg:px-8 lg:py-16">
        {{-- Main message --}}
        <div class="max-w-2xl">
            <div class="flex flex-wrap items-center gap-3">
                @if($setting->logo_path)
                    <img
                        class="h-12 w-12 rounded-2xl border border-slate-200 bg-white object-contain p-1 shadow-sm"
                        src="{{ Storage::url($setting->logo_path) }}"
                        alt="{{ $business->name }} logo"
                    >
                @else
                    <span
                        class="grid h-12 w-12 place-items-center rounded-2xl text-lg font-black text-white shadow-sm"
                        style="background: var(--brand)"
                    >
                        {{ Str::upper(Str::substr($business->name, 0, 1)) }}
                    </span>
                @endif

                <div>
                    <p class="text-sm font-black text-slate-950">{{ $business->name }}</p>
                    <div class="mt-1 flex items-center gap-2 text-xs font-bold">
                        <span class="h-2 w-2 rounded-full {{ $isOpenToday ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        <span class="{{ $isOpenToday ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ $isOpenToday ? 'Open today' : 'Closed today' }}
                        </span>
                    </div>
                </div>
            </div>

            <p class="mt-10 text-xs font-black uppercase tracking-[.24em]" style="color: var(--brand)">
                Appointments made simple
            </p>

            <h1 class="mt-4 max-w-2xl text-5xl font-black leading-[.98] tracking-[-.055em] text-slate-950 sm:text-6xl lg:text-7xl">
                {{ $setting->hero_heading ?: $business->name }}
            </h1>

            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-600 sm:text-xl">
                {{ $setting->hero_subtitle ?: ($business->description ?: 'Professional service, simple online booking, and a time that works for you.') }}
            </p>

            <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                <a
                    class="brand-button inline-flex items-center justify-center gap-2 px-7 py-4 text-base"
                    href="{{ route('public.booking.create', $business) }}"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M7 3v3M17 3v3M4 9h16M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="m9 14 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    {{ $setting->hero_cta_text ?: 'Book an appointment' }}
                </a>

                @if($business->phone)
                    <a
                        class="inline-flex items-center justify-center gap-2 rounded-[var(--button-radius)] border border-slate-200 bg-white px-7 py-4 font-black text-slate-700 shadow-sm transition hover:border-slate-300 hover:text-slate-950"
                        href="tel:{{ App\Support\PhoneNumber::normalize($business->phone) }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8.7 3.8 10.4 7c.3.6.2 1.2-.3 1.7L8.8 10c1.1 2.4 2.8 4.1 5.2 5.2l1.3-1.3c.5-.5 1.1-.6 1.7-.3l3.2 1.7c.7.4 1 1.1.8 1.8l-.5 2c-.2.8-.9 1.3-1.7 1.3C10.4 20.4 3.6 13.6 3.6 5.2c0-.8.5-1.5 1.3-1.7l2-.5c.7-.2 1.4.1 1.8.8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        </svg>
                        Call us
                    </a>
                @endif
            </div>

            <div class="mt-9 flex flex-wrap gap-x-6 gap-y-3 text-sm font-bold text-slate-500">
                <span class="inline-flex items-center gap-2">
                    <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-100 text-[11px] text-emerald-700">✓</span>
                    No account needed
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-100 text-[11px] text-emerald-700">✓</span>
                    Instant confirmation
                </span>
                <span class="inline-flex items-center gap-2">
                    <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-100 text-[11px] text-emerald-700">✓</span>
                    Pick your own time
                </span>
            </div>

            @if($serviceCategories->isNotEmpty())
                <div class="mt-10 border-t border-slate-200 pt-7">
                    <p class="text-xs font-black uppercase tracking-[.18em] text-slate-400">Available appointments</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($serviceCategories as $category)
                            <span class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm">
                                {{ $category->name }}
                            </span>
                        @endforeach

                        @if($categories->count() > 4)
                            <a
                                class="rounded-full px-4 py-2 text-sm font-black"
                                style="background: color-mix(in srgb, var(--brand) 10%, white); color: var(--brand)"
                                href="{{ route('public.booking.create', $business) }}"
                            >
                                +{{ $categories->count() - 4 }} more
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Visual / visit card --}}
        <div class="relative lg:pl-5">
            <div class="relative min-h-[540px] overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-2xl shadow-slate-900/10 sm:min-h-[620px]">
                @if($setting->hero_image_path)
                    <img
                        class="absolute inset-0 h-full w-full object-cover"
                        src="{{ Storage::url($setting->hero_image_path) }}"
                        alt="{{ $business->name }}"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/15 to-slate-950/10"></div>
                @else
                    <div
                        class="absolute inset-0"
                        style="background:
                            radial-gradient(circle at 82% 16%, color-mix(in srgb, var(--accent) 26%, transparent), transparent 32%),
                            radial-gradient(circle at 12% 72%, color-mix(in srgb, var(--brand) 20%, transparent), transparent 38%),
                            linear-gradient(145deg, #ffffff 0%, #f8fafc 48%, #eef2f7 100%);"
                    ></div>

                    <div
                        class="absolute -right-16 top-24 h-64 w-64 rounded-full opacity-15 blur-2xl"
                        style="background: var(--brand)"
                    ></div>

                    <div
                        class="absolute -left-12 bottom-40 h-56 w-56 rounded-full opacity-15 blur-2xl"
                        style="background: var(--accent)"
                    ></div>

                    <div class="absolute right-8 top-24 select-none text-[11rem] font-black leading-none text-slate-900/[.035] sm:text-[15rem]">
                        {{ Str::upper(Str::substr($business->name, 0, 1)) }}
                    </div>
                @endif

                <div class="absolute left-5 top-5 sm:left-6 sm:top-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/40 bg-white/90 px-4 py-2 text-xs font-black text-slate-800 shadow-sm backdrop-blur">
                        <span class="h-2 w-2 rounded-full {{ $isOpenToday ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $isOpenToday ? 'Open today' : 'Closed today' }}
                    </div>
                </div>

                <div class="absolute inset-x-6 bottom-[240px] sm:inset-x-8 sm:bottom-[240px]">
                    @if($serviceCategories->isNotEmpty())
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach($serviceCategories->take(3) as $category)
                                <span class="rounded-full border {{ $setting->hero_image_path ? 'border-white/20 bg-slate-950/35 text-white' : 'border-slate-200 bg-white/80 text-slate-700' }} px-3 py-1.5 text-xs font-black shadow-sm backdrop-blur">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="max-w-md">
                        <p class="text-2xl font-black leading-tight tracking-[-.03em] {{ $setting->hero_image_path ? 'text-white' : 'text-slate-950' }} sm:text-3xl">
                            {{ $business->description ?: 'Care, comfort, and a time that works for you.' }}
                        </p>
                        <p class="mt-3 text-sm font-semibold leading-6 {{ $setting->hero_image_path ? 'text-white/70' : 'text-slate-500' }}">
                            Book your next visit online in just a few clicks.
                        </p>
                    </div>
                </div>

                <div class="absolute inset-x-4 bottom-4 sm:inset-x-6 sm:bottom-6">
                    <div class="rounded-[1.75rem] border border-white/10 bg-slate-950/90 p-5 text-white shadow-xl backdrop-blur-xl sm:p-6">
                        <div class="flex items-center justify-between gap-5">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[.18em] text-white/45">
                                    {{ $today->format('l') }}
                                </p>
                                <p class="mt-1 text-xl font-black tracking-[-.02em]">{{ $todayHours }}</p>
                            </div>

                            <div class="text-right">
                                <p class="text-[11px] font-black uppercase tracking-[.18em] text-white/45">Today</p>
                                <p class="mt-1 text-sm font-bold {{ $isOpenToday ? 'text-emerald-300' : 'text-white/60' }}">
                                    {{ $isOpenToday ? 'Appointments available' : 'See another day' }}
                                </p>
                            </div>
                        </div>

                        @if($business->address || $business->phone)
                            <div class="mt-5 grid gap-4 border-t border-white/10 pt-5 sm:grid-cols-2">
                                @if($business->address)
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-white/45">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/>
                                                <circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.7"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-[.16em] text-white/35">Visit us</p>
                                            <p class="mt-1 line-clamp-2 text-sm font-semibold leading-5 text-white/90">
                                                {{ $business->address }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if($business->phone)
                                    <div class="flex items-start gap-3">
                                        <span class="mt-0.5 text-white/45">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M8.7 3.8 10.4 7c.3.6.2 1.2-.3 1.7L8.8 10c1.1 2.4 2.8 4.1 5.2 5.2l1.3-1.3c.5-.5 1.1-.6 1.7-.3l3.2 1.7c.7.4 1 1.1.8 1.8l-.5 2c-.2.8-.9 1.3-1.7 1.3C10.4 20.4 3.6 13.6 3.6 5.2c0-.8.5-1.5 1.3-1.7l2-.5c.7-.2 1.4.1 1.8.8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-[.16em] text-white/35">Contact</p>
                                            <a
                                                class="mt-1 block text-sm font-semibold text-white/90 hover:text-white"
                                                href="tel:{{ App\Support\PhoneNumber::normalize($business->phone) }}"
                                            >
                                                {{ $business->phone }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>
@endsection

@extends('layouts.public')

@section('content')
    <section class="relative isolate overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 opacity-60" style="background: radial-gradient(circle at 80% 20%, color-mix(in srgb, var(--brand) 75%, transparent), transparent 34%), radial-gradient(circle at 15% 85%, color-mix(in srgb, var(--accent) 55%, transparent), transparent 30%)"></div>
        @if($setting->hero_image_path)
            <img class="absolute inset-0 -z-10 h-full w-full object-cover opacity-30 mix-blend-luminosity" src="{{ Storage::url($setting->hero_image_path) }}" alt="">
        @endif
        <div class="relative mx-auto grid min-h-[680px] max-w-7xl items-center gap-12 px-5 py-24 lg:grid-cols-[1.1fr_.9fr] lg:px-8">
            <div class="max-w-3xl">
                <p class="mb-6 inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[.2em] backdrop-blur">Welcome to {{ $business->name }}</p>
                <h1 class="text-5xl font-black leading-[1.02] tracking-[-.045em] sm:text-6xl lg:text-7xl">{{ $setting->hero_heading ?: $business->name }}</h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">{{ $setting->hero_subtitle ?: $business->description }}</p>
                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <a class="brand-button px-7 py-4 text-base" href="{{ route('public.booking.create', $business) }}">{{ $setting->hero_cta_text }}</a>
                    <a class="inline-flex items-center justify-center rounded-[var(--button-radius)] border border-white/25 bg-white/10 px-7 py-4 font-bold text-white backdrop-blur transition hover:bg-white/20" href="#services">Explore services</a>
                </div>
            </div>
            <div class="relative hidden lg:block">
                <div class="ml-auto max-w-sm rotate-2 rounded-[2.5rem] border border-white/20 bg-white/10 p-7 shadow-2xl backdrop-blur-xl">
                    <div class="grid h-16 w-16 place-items-center rounded-2xl text-2xl font-black text-white" style="background: var(--brand)">{{ Str::upper(Str::substr($business->name, 0, 1)) }}</div>
                    <p class="mt-10 text-sm font-bold uppercase tracking-[.2em] text-white/60">Appointments made simple</p>
                    <p class="mt-3 text-3xl font-black tracking-tight">Choose your service and reserve a time in minutes.</p>
                    <div class="mt-8 flex items-center gap-3 rounded-2xl bg-white/10 p-4">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-950">✓</span>
                        <span class="text-sm font-semibold text-white/90">Instant booking confirmation</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="scroll-mt-24 py-24 sm:py-28">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-black uppercase tracking-[.2em]" style="color: var(--brand)">Our services</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-.035em] text-slate-950 sm:text-5xl">Carefully chosen for you.</h2>
                <p class="mt-5 text-lg leading-8 text-slate-600">Browse our services, compare details, and choose the appointment that feels right.</p>
            </div>

            @forelse($categories as $category)
                <div class="mt-16 first:mt-14">
                    <div class="mb-7 flex items-end justify-between gap-5">
                        <div>
                            <h3 class="text-2xl font-black tracking-tight text-slate-900">{{ $category->name }}</h3>
                            @if($category->description)<p class="mt-2 text-sm text-slate-500">{{ $category->description }}</p>@endif
                        </div>
                    </div>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($category->services as $service)
                            <x-public.service-card :service="$service" :business="$business" />
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="soft-card mt-12 p-10 text-center">
                    <p class="text-lg font-black text-slate-900">Services are being prepared.</p>
                    <p class="mt-2 text-sm text-slate-500">Please contact us and we’ll be happy to help.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section id="about" class="scroll-mt-24 overflow-hidden bg-white py-24 sm:py-28">
        <div class="mx-auto grid max-w-7xl gap-14 px-5 lg:grid-cols-2 lg:items-center lg:px-8">
            <div class="relative min-h-[430px] overflow-hidden rounded-[2.5rem]" style="background: linear-gradient(145deg, color-mix(in srgb, var(--brand) 14%, white), color-mix(in srgb, var(--accent) 24%, white))">
                <div class="absolute -right-12 -top-12 h-56 w-56 rounded-full opacity-35" style="background: var(--accent)"></div>
                <div class="absolute -bottom-14 -left-14 h-64 w-64 rounded-full opacity-30" style="background: var(--brand)"></div>
                <div class="absolute inset-0 grid place-items-center p-12 text-center">
                    <div>
                        <span class="mx-auto grid h-24 w-24 place-items-center rounded-3xl bg-white text-4xl font-black shadow-xl" style="color: var(--brand)">{{ Str::upper(Str::substr($business->name, 0, 1)) }}</span>
                        <p class="mt-7 text-2xl font-black tracking-tight text-slate-900">{{ $business->name }}</p>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-sm font-black uppercase tracking-[.2em]" style="color: var(--brand)">About</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-.035em] text-slate-950 sm:text-5xl">{{ $setting->about_heading }}</h2>
                <p class="mt-6 whitespace-pre-line text-lg leading-8 text-slate-600">{{ $setting->about_body ?: $business->description }}</p>
                <a class="brand-button mt-9 px-7 py-4" href="{{ route('public.booking.create', $business) }}">Plan your visit</a>
            </div>
        </div>
    </section>

    <section class="py-24 sm:py-28">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-black uppercase tracking-[.2em]" style="color: var(--brand)">Why choose us</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-.035em] text-slate-950">A better appointment experience.</h2>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @foreach(($setting->why_choose_us ?: ['Personal attention', 'Easy online booking', 'A warm welcome']) as $index => $point)
                    <div class="soft-card p-7">
                        <span class="grid h-12 w-12 place-items-center rounded-2xl text-lg font-black" style="background: color-mix(in srgb, var(--brand) 12%, white); color: var(--brand)">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h3 class="mt-7 text-lg font-black text-slate-900">{{ $point }}</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Every detail is designed to make your visit feel clear, comfortable, and considered.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="contact" class="scroll-mt-24 bg-slate-950 py-24 text-white sm:py-28">
        <div class="mx-auto grid max-w-7xl gap-14 px-5 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
            <div>
                <p class="text-sm font-black uppercase tracking-[.2em]" style="color: var(--accent)">Visit us</p>
                <h2 class="mt-4 text-4xl font-black tracking-[-.035em] sm:text-5xl">We’d love to welcome you.</h2>
                <div class="mt-9 space-y-5 text-slate-300">
                    @if($business->address)<p class="whitespace-pre-line leading-7">{{ $business->address }}</p>@endif
                    @if($business->phone)<p><a class="font-bold text-white hover:underline" href="tel:{{ App\Support\PhoneNumber::normalize($business->phone) }}">{{ $business->phone }}</a></p>@endif
                    @if($business->email)<p><a class="font-bold text-white hover:underline" href="mailto:{{ $business->email }}">{{ $business->email }}</a></p>@endif
                    @if($business->whatsapp)<a class="inline-flex items-center gap-2 font-bold" style="color: var(--accent)" href="https://wa.me/{{ App\Support\PhoneNumber::normalize($business->whatsapp) }}" rel="noopener">Message us on WhatsApp →</a>@endif
                </div>
            </div>
            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-7 backdrop-blur sm:p-9">
                <p class="text-lg font-black">Business hours</p>
                <div class="mt-6 divide-y divide-white/10">
                    @foreach([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 0 => 'Sunday'] as $day => $label)
                        @php($opening = $hours->get($day))
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <span class="font-semibold text-slate-300">{{ $label }}</span>
                            <span class="text-right text-white">{{ !$opening || $opening->is_closed ? 'Closed' : Carbon\CarbonImmutable::parse($opening->opens_at)->format('g:i A').' – '.Carbon\CarbonImmutable::parse($opening->closes_at)->format('g:i A') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

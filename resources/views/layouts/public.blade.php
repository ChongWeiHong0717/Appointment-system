<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $setting->meta_description ?: $business->description }}">
    <meta name="theme-color" content="{{ $setting->primary_color }}">
    <title>@yield('title', $setting->meta_title ?: $business->name)</title>
    <style>
        :root {
            --brand: {{ $setting->primary_color }};
            --accent: {{ $setting->accent_color }};
            --button-radius: {{ $setting->button_style === 'pill' ? '9999px' : ($setting->button_style === 'square' ? '.35rem' : '.9rem') }};
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50">
    <header x-data="{ open: false }" class="relative z-50 border-b border-black/5 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
            <a href="{{ route('public.home', $business) }}" class="flex min-w-0 items-center gap-3" aria-label="{{ $business->name }} home">
                @if($business->logo_path)
                    <img class="h-11 w-11 rounded-2xl object-cover" src="{{ Storage::url($business->logo_path) }}" alt="{{ $business->name }} logo">
                @else
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl text-lg font-black text-white" style="background: var(--brand)">
                        {{ Str::upper(Str::substr($business->name, 0, 1)) }}
                    </span>
                @endif
                <span class="truncate text-base font-black tracking-tight text-slate-900 sm:text-lg">{{ $business->name }}</span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex">
                <a class="transition hover:text-slate-950" href="{{ route('public.home', $business) }}">Home</a>
                <a class="transition hover:text-slate-950" href="{{ route('public.home', $business) }}#services">Services</a>
                <a class="transition hover:text-slate-950" href="{{ route('public.home', $business) }}#about">About</a>
                <a class="transition hover:text-slate-950" href="{{ route('public.home', $business) }}#contact">Contact</a>
                <a class="brand-button px-5 py-2.5" href="{{ route('public.booking.create', $business) }}">Book appointment</a>
            </nav>

            <button @click="open = !open" class="grid h-11 w-11 place-items-center rounded-xl border border-slate-200 text-slate-700 md:hidden" type="button" aria-label="Toggle navigation">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
        <nav x-cloak x-show="open" x-transition class="border-t border-slate-100 bg-white px-5 py-5 md:hidden">
            <div class="mx-auto grid max-w-7xl gap-1 text-sm font-bold text-slate-700">
                <a @click="open = false" class="rounded-xl px-3 py-3 hover:bg-slate-50" href="{{ route('public.home', $business) }}">Home</a>
                <a @click="open = false" class="rounded-xl px-3 py-3 hover:bg-slate-50" href="{{ route('public.home', $business) }}#services">Services</a>
                <a @click="open = false" class="rounded-xl px-3 py-3 hover:bg-slate-50" href="{{ route('public.home', $business) }}#about">About</a>
                <a @click="open = false" class="rounded-xl px-3 py-3 hover:bg-slate-50" href="{{ route('public.home', $business) }}#contact">Contact</a>
                <a class="brand-button mt-3 px-5 py-3" href="{{ route('public.booking.create', $business) }}">Book appointment</a>
            </div>
        </nav>
    </header>

    <main>@yield('content')</main>

    <footer class="border-t border-white/10 bg-slate-950 text-slate-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 md:grid-cols-3 lg:px-8">
            <div>
                <p class="text-xl font-black text-white">{{ $business->name }}</p>
                <p class="mt-3 max-w-sm text-sm leading-6 text-slate-400">{{ $business->description }}</p>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-[.2em] text-white">Visit</p>
                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-400">{{ $business->address ?: 'Contact us for our location.' }}</p>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-[.2em] text-white">Contact</p>
                <div class="mt-3 space-y-2 text-sm text-slate-400">
                    @if($business->phone)<p><a class="hover:text-white" href="tel:{{ App\Support\PhoneNumber::normalize($business->phone) }}">{{ $business->phone }}</a></p>@endif
                    @if($business->email)<p><a class="hover:text-white" href="mailto:{{ $business->email }}">{{ $business->email }}</a></p>@endif
                </div>
            </div>
        </div>
        <div class="border-t border-white/10 px-5 py-5 text-center text-xs text-slate-500">© {{ date('Y') }} {{ $business->name }}. All rights reserved.</div>
    </footer>
</body>
</html>

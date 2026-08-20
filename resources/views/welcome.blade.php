<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bookwise — Appointments made effortless</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="relative isolate min-h-screen overflow-hidden">
        <div class="absolute inset-0 -z-10 opacity-60" style="background: radial-gradient(circle at 75% 20%, #0f766e 0, transparent 32%), radial-gradient(circle at 20% 85%, #7c3aed 0, transparent 30%)"></div>
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-6 lg:px-8">
            <a class="flex items-center gap-3 text-lg font-black" href="/">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white text-slate-950">B</span> Bookwise
            </a>
            <a class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-bold backdrop-blur hover:bg-white/20" href="{{ route('login') }}">Business login</a>
        </nav>
        <section class="mx-auto grid max-w-7xl items-center gap-14 px-5 py-24 lg:grid-cols-[1.1fr_.9fr] lg:px-8 lg:py-32">
            <div>
                <p class="inline-flex rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[.2em]">Multi-business appointment platform</p>
                <h1 class="mt-7 max-w-4xl text-5xl font-black leading-[1.02] tracking-[-.05em] sm:text-7xl">Beautiful booking. Calm, capable operations.</h1>
                <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-300">Professional public websites, simple online reservations, and focused front-desk tools—built to adapt to every service business.</p>
                <a class="mt-10 inline-flex rounded-xl bg-white px-6 py-4 font-black text-slate-950 shadow-xl transition hover:-translate-y-0.5" href="{{ route('login') }}">Open business dashboard →</a>
            </div>
            <div class="rounded-[2.5rem] border border-white/15 bg-white/10 p-7 shadow-2xl backdrop-blur-xl sm:p-9">
                <p class="text-sm font-black uppercase tracking-[.2em] text-emerald-300">Demo websites</p>
                <div class="mt-6 space-y-4">
                    <a class="block rounded-2xl border border-white/10 bg-white/10 p-5 transition hover:bg-white/15" href="{{ url('/happy-paws') }}"><span class="block font-black">Happy Paws Grooming</span><span class="mt-1 block text-sm text-slate-300">Grooming services · teal theme</span></a>
                    <a class="block rounded-2xl border border-white/10 bg-white/10 p-5 transition hover:bg-white/15" href="{{ url('/glow-beauty') }}"><span class="block font-black">Glow Beauty Studio</span><span class="mt-1 block text-sm text-slate-300">Facial and massage · violet theme</span></a>
                </div>
                <p class="mt-6 text-xs leading-5 text-slate-400">Run the demo seeder first to open these sites locally.</p>
            </div>
        </section>
    </main>
</body>
</html>

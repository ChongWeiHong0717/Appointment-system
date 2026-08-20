<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Platform') — Bookwise</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900" x-data="{ sidebarOpen: false }">
    <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-950 text-white transition-transform duration-300 lg:translate-x-0">
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-6">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-500 text-lg font-black text-slate-950">P</span>
            <div><p class="text-sm font-black">Bookwise Platform</p><p class="text-xs text-slate-500">Super administration</p></div>
        </div>

        <nav class="flex-1 space-y-1 px-4 py-6 text-sm font-bold">
            <a class="flex items-center gap-3 rounded-xl px-4 py-3 transition {{ request()->routeIs('platform.businesses.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}" href="{{ route('platform.businesses.index') }}">
                <span class="h-2 w-2 rounded-full {{ request()->routeIs('platform.businesses.*') ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>Businesses
            </a>
        </nav>

        <div class="border-t border-white/10 p-4">
            <a class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-slate-300 hover:bg-white/10 hover:text-white" href="{{ url('/') }}" target="_blank">Open main site <span>↗</span></a>
            <form action="{{ route('platform.logout') }}" method="POST">@csrf<button class="mt-1 w-full rounded-xl px-4 py-3 text-left text-sm font-bold text-slate-400 hover:bg-white/10 hover:text-white" type="submit">Sign out</button></form>
        </div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/90 px-5 backdrop-blur-xl sm:px-8">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 lg:hidden" type="button" aria-label="Open navigation"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                <div><p class="text-xs font-bold uppercase tracking-[.18em] text-slate-400">{{ now()->format('l, j F') }}</p><p class="mt-1 text-sm font-black text-slate-900">@yield('header', 'Platform control')</p></div>
            </div>
            <div class="hidden text-right sm:block"><p class="text-sm font-black">{{ Auth::guard('platform')->user()->name }}</p><p class="text-xs text-slate-400">Platform admin</p></div>
        </header>

        @foreach(['success' => 'bg-emerald-600', 'warning' => 'bg-amber-500 text-slate-950'] as $flash => $classes)
            @if(session($flash))
                <div x-data="{ show: true }" x-show="show" x-transition class="fixed right-5 top-24 z-50 max-w-md rounded-2xl px-5 py-4 text-sm font-bold text-white shadow-2xl {{ $classes }}"><button @click="show = false" class="mr-3 opacity-70" type="button">×</button>{{ session($flash) }}</div>
            @endif
        @endforeach

        <main class="p-5 sm:p-8 lg:p-10">@yield('content')</main>
    </div>
</body>
</html>

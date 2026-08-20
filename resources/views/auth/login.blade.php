<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Business login — Bookwise</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white">
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="relative hidden overflow-hidden p-12 lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-70" style="background: radial-gradient(circle at 25% 20%, #0f766e, transparent 35%), radial-gradient(circle at 80% 80%, #7c3aed, transparent 34%)"></div>
            <a class="relative flex items-center gap-3 text-lg font-black" href="{{ url('/') }}"><span class="grid h-11 w-11 place-items-center rounded-xl bg-white text-slate-950">B</span> Bookwise</a>
            <div class="relative max-w-xl">
                <p class="text-sm font-black uppercase tracking-[.2em] text-emerald-300">Business workspace</p>
                <h1 class="mt-5 text-5xl font-black leading-tight tracking-[-.045em]">Everything today needs, in one calm view.</h1>
                <p class="mt-6 text-lg leading-8 text-slate-300">Manage appointments, welcome customers, and keep your public website up to date.</p>
            </div>
            <p class="relative text-xs text-slate-500">Secure multi-business administration</p>
        </section>

        <section class="flex items-center justify-center bg-stone-50 px-5 py-14 text-slate-900 sm:px-10">
            <div class="w-full max-w-md">
                <a class="mb-12 flex items-center gap-3 text-lg font-black lg:hidden" href="{{ url('/') }}"><span class="grid h-10 w-10 place-items-center rounded-xl bg-slate-950 text-white">B</span> Bookwise</a>
                <p class="text-sm font-black uppercase tracking-[.2em] text-emerald-700">Welcome back</p>
                <h2 class="mt-3 text-4xl font-black tracking-[-.04em] text-slate-950">Sign in to your business</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">Use the administrator details assigned to your business.</p>

                <form class="mt-9 space-y-6" action="{{ route('login.store') }}" method="POST">
                    @csrf
                    <div>
                        <label class="form-label" for="email">Email address</label>
                        <input class="form-input @error('email') border-rose-400 @enderror" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        @error('email')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input @error('password') border-rose-400 @enderror" id="password" name="password" type="password" autocomplete="current-password" required>
                        @error('password')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-3 text-sm font-semibold text-slate-600">
                        <input class="h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600" name="remember" type="checkbox" value="1"> Remember me on this device
                    </label>
                    <button class="brand-button w-full px-6 py-3.5" style="--brand: #047857" type="submit">Sign in</button>
                </form>
                <p class="mt-8 text-center text-xs leading-5 text-slate-400">Need access or a password reset? Contact your platform administrator.</p>
            </div>
        </section>
    </main>
</body>
</html>

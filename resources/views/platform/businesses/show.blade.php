@extends('layouts.platform')
@section('title', $business->name)
@section('header', 'Business access')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a class="text-sm font-black text-emerald-700 hover:underline" href="{{ route('platform.businesses.index') }}">← Businesses</a>
            <div class="mt-3 flex flex-wrap items-center gap-3"><h1 class="text-3xl font-black tracking-[-.035em] text-slate-950 sm:text-4xl">{{ $business->name }}</h1><span class="rounded-full px-3 py-1 text-xs font-black {{ $business->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $business->is_active ? 'Active' : 'Suspended' }}</span></div>
            <p class="mt-2 text-slate-500">Platform controls only. Business content stays in the tenant's own admin workspace.</p>
        </div>
        <div class="flex flex-wrap gap-2"><a class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('public.home', $business) }}" target="_blank">Open public site ↗</a><a class="brand-button px-4 py-3 text-sm" style="--brand:#047857" href="{{ route('login', ['business' => $business->slug]) }}" target="_blank">Open admin login ↗</a></div>
    </div>
    <x-admin.form-errors />

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">Public path</p><p class="mt-2 break-all font-black text-slate-900">/{{ $business->slug }}</p></div>
                    <div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">Admins</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $admins->count() }}</p></div>
                    <div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">Services</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $business->services_count }}</p></div>
                    <div><p class="text-xs font-black uppercase tracking-[.16em] text-slate-400">Appointments</p><p class="mt-2 text-2xl font-black text-slate-900">{{ $business->appointments_count }}</p></div>
                </div>
                <div class="mt-6 grid gap-4 border-t border-slate-100 pt-6 text-sm sm:grid-cols-2">
                    <div><p class="font-black text-slate-700">Contact</p><p class="mt-1 text-slate-500">{{ $business->email ?: 'No email set' }}<br>{{ $business->phone ?: 'No phone set' }}</p></div>
                    <div><p class="font-black text-slate-700">Configuration</p><p class="mt-1 text-slate-500">{{ $business->timezone }}<br>{{ $business->booking_interval_minutes }}-minute booking interval</p></div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-black uppercase tracking-[.2em] text-emerald-700">Access</p><h2 class="mt-2 text-2xl font-black text-slate-950">Business administrators</h2></div><p class="text-sm text-slate-500">{{ $admins->where('is_active', true)->count() }} enabled</p></div>

                <div class="mt-6 space-y-4">
                    @forelse($admins as $admin)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div><div class="flex flex-wrap items-center gap-2"><p class="font-black text-slate-900">{{ $admin->name }}</p><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $admin->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $admin->is_active ? 'Enabled' : 'Disabled' }}</span></div><p class="mt-1 text-sm text-slate-500">{{ $admin->email }}</p></div>
                                <div class="flex flex-wrap gap-2">
                                    <form action="{{ route('platform.businesses.admins.status', [$business, $admin]) }}" method="POST">@csrf @method('PATCH')<input name="is_active" type="hidden" value="{{ $admin->is_active ? 0 : 1 }}"><button class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" type="submit">{{ $admin->is_active ? 'Disable' : 'Enable' }}</button></form>
                                    <form action="{{ route('platform.businesses.admins.destroy', [$business, $admin]) }}" method="POST" onsubmit="return confirm('Permanently delete this business administrator?')">@csrf @method('DELETE')<button class="rounded-xl px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button></form>
                                </div>
                            </div>
                            <details class="mt-4 border-t border-slate-100 pt-4"><summary class="cursor-pointer text-sm font-black text-emerald-700">Reset password</summary><form class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]" action="{{ route('platform.businesses.admins.password', [$business, $admin]) }}" method="POST">@csrf @method('PUT')<input class="form-input" name="password" type="password" minlength="12" placeholder="New password" required><input class="form-input" name="password_confirmation" type="password" minlength="12" placeholder="Confirm password" required><button class="brand-button px-4 py-3 text-sm" style="--brand:#047857" type="submit">Reset</button></form></details>
                        </article>
                    @empty
                        <div class="rounded-2xl bg-amber-50 p-5 text-sm text-amber-800"><p class="font-black">No administrator yet</p><p class="mt-1">Create one before handing this business to its team.</p></div>
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <form class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('platform.businesses.admins.store', $business) }}" method="POST">
                @csrf
                <p class="text-sm font-black uppercase tracking-[.2em] text-emerald-700">New account</p><h2 class="mt-2 text-xl font-black text-slate-950">Create business admin</h2>
                <div class="mt-5 space-y-4"><div><label class="form-label" for="name">Name</label><input class="form-input" id="name" name="name" value="{{ old('name') }}" maxlength="120" required></div><div><label class="form-label" for="email">Email</label><input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" required></div><div><label class="form-label" for="password">Temporary password</label><input class="form-input" id="password" name="password" type="password" minlength="12" required></div><div><label class="form-label" for="password_confirmation">Confirm password</label><input class="form-input" id="password_confirmation" name="password_confirmation" type="password" minlength="12" required></div></div>
                <button class="brand-button mt-5 w-full px-5 py-3" style="--brand:#047857" type="submit">Create administrator</button>
            </form>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-black uppercase tracking-[.2em] text-slate-400">Tenant status</p><h2 class="mt-2 text-xl font-black text-slate-950">{{ $business->is_active ? 'Business is active' : 'Business is suspended' }}</h2><p class="mt-2 text-sm leading-6 text-slate-500">Suspension blocks the public site, bookings, and business-admin access while preserving all records.</p>
                <form class="mt-5" action="{{ route('platform.businesses.status', $business) }}" method="POST" onsubmit="return confirm('{{ $business->is_active ? 'Suspend this business and block access?' : 'Reactivate this business?' }}')">@csrf @method('PATCH')<input name="is_active" type="hidden" value="{{ $business->is_active ? 0 : 1 }}"><button class="w-full rounded-xl px-5 py-3 text-sm font-black {{ $business->is_active ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}" type="submit">{{ $business->is_active ? 'Suspend business' : 'Reactivate business' }}</button></form>
            </div>

            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-6"><p class="text-sm font-black uppercase tracking-[.2em] text-rose-700">Danger zone</p><p class="mt-3 text-sm leading-6 text-rose-800">Deletion is permanent only when there is no appointment history. Otherwise V2 safely suspends the business.</p><form class="mt-4" action="{{ route('platform.businesses.destroy', $business) }}" method="POST" onsubmit="return confirm('Delete this business? Businesses with appointment history will be suspended instead.')">@csrf @method('DELETE')<button class="w-full rounded-xl bg-rose-700 px-5 py-3 text-sm font-black text-white hover:bg-rose-800" type="submit">Delete business</button></form></div>
        </aside>
    </div>
</div>
@endsection

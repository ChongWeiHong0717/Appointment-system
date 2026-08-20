@extends('layouts.platform')
@section('title', 'Create business')
@section('header', 'Business provisioning')

@section('content')
<div class="mx-auto max-w-4xl">
    <x-admin.page-header eyebrow="Platform" title="Create a business" description="Create the tenant shell here. Its team can complete business content and branding inside the existing business admin area." />

    <form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('platform.businesses.store') }}" method="POST">
        @csrf
        <x-admin.form-errors />
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2"><label class="form-label" for="name">Business name</label><input class="form-input" id="name" name="name" value="{{ old('name') }}" required maxlength="150" autofocus></div>
            <div class="sm:col-span-2"><label class="form-label" for="slug">Public URL slug</label><div class="flex items-center rounded-xl border border-slate-200 bg-white shadow-sm focus-within:ring-2 focus-within:ring-emerald-300"><span class="pl-4 text-sm text-slate-400">{{ url('/') }}/</span><input class="min-w-0 flex-1 rounded-xl border-0 px-1 py-3 pr-4 outline-none" id="slug" name="slug" value="{{ old('slug') }}" placeholder="generated-from-name" maxlength="160"></div><p class="mt-2 text-xs text-slate-400">Leave blank to generate it from the business name.</p></div>
            <div><label class="form-label" for="email">Business email</label><input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255"></div>
            <div><label class="form-label" for="phone">Business phone</label><input class="form-input" id="phone" name="phone" value="{{ old('phone') }}" maxlength="40"></div>
            <div class="sm:col-span-2"><label class="form-label" for="address">Address</label><textarea class="form-input min-h-24" id="address" name="address" maxlength="2000">{{ old('address') }}</textarea></div>
            <div><label class="form-label" for="timezone">Timezone</label><select class="form-input" id="timezone" name="timezone" required>@foreach($timezones as $timezone)<option value="{{ $timezone }}" @selected(old('timezone', 'Asia/Kuala_Lumpur') === $timezone)>{{ $timezone }}</option>@endforeach</select></div>
            <div><label class="form-label" for="booking_interval_minutes">Booking interval</label><select class="form-input" id="booking_interval_minutes" name="booking_interval_minutes" required>@foreach([15, 30, 45, 60, 90, 120] as $minutes)<option value="{{ $minutes }}" @selected((int) old('booking_interval_minutes', 30) === $minutes)>{{ $minutes }} minutes</option>@endforeach</select></div>
        </div>
        <div class="mt-8 flex items-center justify-end gap-3 border-t border-slate-100 pt-6"><a class="rounded-xl px-5 py-3 text-sm font-black text-slate-500 hover:bg-slate-50" href="{{ route('platform.businesses.index') }}">Cancel</a><button class="brand-button px-6 py-3" style="--brand:#047857" type="submit">Create business</button></div>
    </form>
</div>
@endsection

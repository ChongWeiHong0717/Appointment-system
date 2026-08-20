@extends('layouts.public')

@section('title', 'Book an appointment — '.$business->name)

@section('content')
@php
    $serviceId = (int) old('service_id', $selectedServiceId);
    $categoryId = $categories->first(fn ($category) => $category->services->contains('id', $serviceId))?->id;
    $bookingCategories = $categories->map(fn ($category) => [
        'id' => $category->id,
        'name' => $category->name,
        'description' => $category->description,
        'services' => $category->services->map(fn ($service) => [
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'duration' => $service->duration_minutes,
            'price' => $service->price === null ? null : (float) $service->price,
            'priceLabel' => $service->price === null ? 'Price on request' : 'RM'.number_format((float) $service->price, 2),
        ])->values(),
    ])->values();
@endphp

<section class="min-h-[75vh] py-10 sm:py-16"
    x-data="bookingFlow({
        categories: {{ Js::from($bookingCategories) }},
        slotsUrl: {{ Js::from(route('public.booking.slots', $business)) }},
        initialCategory: {{ Js::from($categoryId) }},
        initialService: {{ Js::from($serviceId ?: null) }},
        initialDate: {{ Js::from(old('appointment_date')) }},
        initialTime: {{ Js::from(old('start_time')) }},
        initialStep: {{ $errors->any() ? 5 : ($serviceId ? 2 : 1) }},
        customerName: {{ Js::from(old('customer_name')) }},
        customerPhone: {{ Js::from(old('customer_phone')) }},
        customerEmail: {{ Js::from(old('customer_email')) }},
        customerNotes: {{ Js::from(old('customer_notes')) }},
    })">
    <div class="mx-auto max-w-5xl px-5 lg:px-8">
        <div class="mb-9 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a class="text-sm font-bold text-slate-500 hover:text-slate-900" href="{{ route('public.home', $business) }}">← Back to {{ $business->name }}</a>
                <h1 class="mt-4 text-3xl font-black tracking-[-.035em] text-slate-950 sm:text-4xl">Book your appointment</h1>
                <p class="mt-2 text-slate-600">Choose what you need and find a time that suits you.</p>
            </div>
            <p class="text-sm font-bold text-slate-500"><span x-text="step"></span> of 6</p>
        </div>

        <div class="mb-7 grid grid-cols-6 gap-2" aria-label="Booking progress">
            <template x-for="index in 6" :key="index">
                <div class="h-1.5 rounded-full transition" :style="index <= step ? 'background: var(--brand)' : 'background: rgb(226 232 240)'" :aria-current="index === step ? 'step' : null"></div>
            </template>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 text-sm text-rose-800">
                <p class="font-black">Please review the highlighted details.</p>
                <ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.booking.store', $business) }}" class="soft-card overflow-hidden">
            @csrf
            <input type="hidden" name="service_id" :value="selectedServiceId">
            <input type="hidden" name="start_time" :value="selectedTime">

            <div class="p-6 sm:p-10">
                <section x-show="step === 1" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 1</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Choose a category</h2>
                    <p class="mt-2 text-sm text-slate-500">Start with the type of appointment you’re looking for.</p>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <template x-for="category in categories" :key="category.id">
                            <button @click="chooseCategory(category.id)" type="button" class="rounded-2xl border p-5 text-left transition hover:-translate-y-0.5 hover:shadow-md" :class="selectedCategoryId === category.id ? 'border-transparent ring-2' : 'border-slate-200 bg-white'" :style="selectedCategoryId === category.id ? 'background: color-mix(in srgb, var(--brand) 8%, white); --tw-ring-color: var(--brand)' : ''">
                                <span class="block text-lg font-black text-slate-900" x-text="category.name"></span>
                                <span class="mt-2 block text-sm leading-6 text-slate-500" x-text="category.description || `${category.services.length} services available`"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="categories.length === 0" class="mt-8 rounded-2xl bg-slate-50 p-8 text-center">
                        <p class="font-black text-slate-800">Online services are not available yet.</p>
                        <p class="mt-2 text-sm text-slate-500">Please contact the business to make an appointment.</p>
                    </div>
                </section>

                <section x-cloak x-show="step === 2" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 2</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Choose a service</h2>
                    <p class="mt-2 text-sm text-slate-500">Select the treatment or service you’d like to reserve.</p>
                    <div class="mt-8 space-y-3">
                        <template x-for="service in services" :key="service.id">
                            <button @click="chooseService(service.id)" type="button" class="flex w-full items-start justify-between gap-5 rounded-2xl border p-5 text-left transition hover:shadow-md" :class="selectedServiceId === service.id ? 'border-transparent ring-2' : 'border-slate-200 bg-white'" :style="selectedServiceId === service.id ? 'background: color-mix(in srgb, var(--brand) 8%, white); --tw-ring-color: var(--brand)' : ''">
                                <span>
                                    <span class="block font-black text-slate-900" x-text="service.name"></span>
                                    <span class="mt-1 block text-sm leading-6 text-slate-500" x-text="service.description"></span>
                                    <span class="mt-3 block text-xs font-bold text-slate-500" x-text="`${service.duration} minutes`"></span>
                                </span>
                                <span class="shrink-0 text-sm font-black" style="color: var(--brand)" x-text="service.priceLabel"></span>
                            </button>
                        </template>
                    </div>
                </section>

                <section x-cloak x-show="step === 3" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 3</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Choose a date</h2>
                    <p class="mt-2 text-sm text-slate-500">Available times will be calculated from business hours and existing appointments.</p>
                    <div class="mt-8 max-w-md">
                        <label class="form-label" for="appointment_date">Appointment date</label>
                        <input @change="loadSlots" x-model="appointmentDate" class="form-input" id="appointment_date" name="appointment_date" type="date" min="{{ now($business->timezone)->toDateString() }}" max="{{ now($business->timezone)->addDays(120)->toDateString() }}" required>
                    </div>
                </section>

                <section x-cloak x-show="step === 4" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 4</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Choose a time</h2>
                    <p class="mt-2 text-sm text-slate-500">Times shown are in {{ str_replace('_', ' ', $business->timezone) }}.</p>

                    <div x-show="loadingSlots" class="mt-8 flex items-center gap-3 text-sm font-bold text-slate-500">
                        <span class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-slate-700"></span> Finding available times…
                    </div>
                    <p x-show="slotError" class="mt-8 rounded-xl bg-rose-50 p-4 text-sm font-bold text-rose-700" x-text="slotError"></p>
                    <div x-show="!loadingSlots && !slotError && slots.length" class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        <template x-for="slot in slots" :key="slot.value">
                            <button @click="selectedTime = slot.value" type="button" class="rounded-xl border px-4 py-3 text-sm font-black transition" :class="selectedTime === slot.value ? 'border-transparent text-white' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400'" :style="selectedTime === slot.value ? 'background: var(--brand)' : ''" x-text="slot.label"></button>
                        </template>
                    </div>
                    <div x-show="!loadingSlots && !slotError && slots.length === 0" class="mt-8 rounded-2xl bg-slate-50 p-8 text-center">
                        <p class="font-black text-slate-800">No times are available on this date.</p>
                        <button @click="next(3)" class="mt-3 text-sm font-black" style="color: var(--brand)" type="button">Choose another date</button>
                    </div>
                </section>

                <section x-cloak x-show="step === 5" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 5</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Your details</h2>
                    <p class="mt-2 text-sm text-slate-500">No account is needed. We’ll use these details for this appointment.</p>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2">
                        <div><label class="form-label" for="customer_name">Full name</label><input x-model="customerName" class="form-input @error('customer_name') border-rose-400 @enderror" id="customer_name" name="customer_name" autocomplete="name" required maxlength="120"></div>
                        <div><label class="form-label" for="customer_phone">Phone number</label><input x-model="customerPhone" class="form-input @error('customer_phone') border-rose-400 @enderror" id="customer_phone" name="customer_phone" autocomplete="tel" required maxlength="40"></div>
                        <div class="sm:col-span-2"><label class="form-label" for="customer_email">Email <span class="font-normal text-slate-400">(optional)</span></label><input x-model="customerEmail" class="form-input @error('customer_email') border-rose-400 @enderror" id="customer_email" name="customer_email" type="email" autocomplete="email" maxlength="255"></div>
                        <div class="sm:col-span-2"><label class="form-label" for="customer_notes">Notes <span class="font-normal text-slate-400">(optional)</span></label><textarea x-model="customerNotes" class="form-input min-h-28 @error('customer_notes') border-rose-400 @enderror" id="customer_notes" name="customer_notes" maxlength="2000" placeholder="Anything the team should know before your visit?"></textarea></div>
                    </div>
                </section>

                <section x-cloak x-show="step === 6" x-transition.opacity>
                    <p class="text-xs font-black uppercase tracking-[.2em]" style="color: var(--brand)">Step 6</p>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-950">Confirm your appointment</h2>
                    <p class="mt-2 text-sm text-slate-500">Please check the details below before confirming.</p>
                    <dl class="mt-8 divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60">
                        <div class="grid gap-1 px-5 py-4 sm:grid-cols-[160px_1fr]"><dt class="text-sm font-bold text-slate-500">Business</dt><dd class="font-black text-slate-900">{{ $business->name }}</dd></div>
                        <div class="grid gap-1 px-5 py-4 sm:grid-cols-[160px_1fr]"><dt class="text-sm font-bold text-slate-500">Service</dt><dd><span class="font-black text-slate-900" x-text="selectedService?.name"></span><span class="ml-2 text-sm text-slate-500" x-text="selectedService ? `(${selectedService.duration} min · ${selectedService.priceLabel})` : ''"></span></dd></div>
                        <div class="grid gap-1 px-5 py-4 sm:grid-cols-[160px_1fr]"><dt class="text-sm font-bold text-slate-500">Date and time</dt><dd class="font-black text-slate-900"><span x-text="appointmentDate"></span> at <span x-text="selectedTime"></span></dd></div>
                        <div class="grid gap-1 px-5 py-4 sm:grid-cols-[160px_1fr]"><dt class="text-sm font-bold text-slate-500">Customer</dt><dd class="font-black text-slate-900"><span x-text="customerName"></span><span class="mt-1 block text-sm font-normal text-slate-500" x-text="customerPhone"></span></dd></div>
                    </dl>
                    <p class="mt-5 text-xs leading-5 text-slate-500">By confirming, you agree to provide accurate contact details and contact the business if you need to change your appointment.</p>
                </section>
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-slate-100 bg-slate-50/70 px-6 py-5 sm:px-10">
                <button x-show="step > 1" @click="next(step - 1)" class="rounded-xl px-4 py-3 text-sm font-black text-slate-600 hover:bg-white hover:text-slate-950" type="button">← Back</button>
                <span x-show="step === 1"></span>
                <button x-show="step === 1" @click="next(2)" :disabled="!selectedCategoryId" class="brand-button px-6 py-3" type="button">Continue</button>
                <button x-cloak x-show="step === 2" @click="next(3)" :disabled="!selectedServiceId" class="brand-button px-6 py-3" type="button">Continue</button>
                <button x-cloak x-show="step === 3" @click="next(4)" :disabled="!appointmentDate" class="brand-button px-6 py-3" type="button">See times</button>
                <button x-cloak x-show="step === 4" @click="next(5)" :disabled="!selectedTime" class="brand-button px-6 py-3" type="button">Continue</button>
                <button x-cloak x-show="step === 5" @click="next(6)" :disabled="!customerName.trim() || !customerPhone.trim()" class="brand-button px-6 py-3" type="button">Review booking</button>
                <button x-cloak x-show="step === 6" class="brand-button px-6 py-3" type="submit">Confirm appointment</button>
            </div>
        </form>
    </div>
</section>
@endsection

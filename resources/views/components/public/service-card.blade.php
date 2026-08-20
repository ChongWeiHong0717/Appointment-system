@props(['service', 'business'])

<article class="group overflow-hidden rounded-3xl border border-black/5 bg-white shadow-[0_14px_50px_rgba(15,23,42,0.06)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_65px_rgba(15,23,42,0.12)]">
    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
        @if($service->image_path)
            <img class="h-full w-full object-cover transition duration-500 group-hover:scale-105" src="{{ Storage::url($service->image_path) }}" alt="{{ $service->name }}">
        @else
            <div class="grid h-full place-items-center" style="background: linear-gradient(135deg, color-mix(in srgb, var(--brand) 14%, white), color-mix(in srgb, var(--accent) 20%, white))">
                <span class="grid h-16 w-16 place-items-center rounded-full bg-white/75 text-2xl font-black shadow-sm" style="color: var(--brand)">{{ Str::upper(Str::substr($service->name, 0, 1)) }}</span>
            </div>
        @endif
        <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-slate-700 shadow-sm backdrop-blur">{{ $service->duration_minutes }} min</span>
    </div>
    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-black tracking-tight text-slate-900">{{ $service->name }}</h3>
            @if($service->price !== null)
                <p class="shrink-0 text-sm font-black" style="color: var(--brand)">RM{{ number_format((float) $service->price, 0) }}</p>
            @endif
        </div>
        <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">{{ $service->description ?: 'Learn more and choose a convenient appointment time.' }}</p>
        <a class="mt-5 inline-flex items-center gap-2 text-sm font-black" style="color: var(--brand)" href="{{ route('public.booking.create', [$business, 'service' => $service->id]) }}">
            Book this service
            <span aria-hidden="true">→</span>
        </a>
    </div>
</article>

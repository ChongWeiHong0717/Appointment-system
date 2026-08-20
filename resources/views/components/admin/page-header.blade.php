@props(['eyebrow', 'title', 'description' => null])

<div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-black uppercase tracking-[.2em] text-emerald-700">{{ $eyebrow }}</p>
        <h1 class="mt-2 text-3xl font-black tracking-[-.035em] text-slate-950 sm:text-4xl">{{ $title }}</h1>
        @if($description)<p class="mt-2 max-w-2xl text-slate-500">{{ $description }}</p>@endif
    </div>
    @if(isset($actions))<div class="shrink-0">{{ $actions }}</div>@endif
</div>

@extends('layouts.admin')
@section('title', 'Categories')
@section('header', 'Service catalog')

@section('content')
<div class="mx-auto max-w-7xl">
    <x-admin.page-header eyebrow="Catalog" title="Categories" description="Organise services into clear groups for customers and your team.">
        <x-slot:actions><a class="brand-button px-5 py-3" style="--brand:#047857" href="{{ route('admin.categories.create') }}">+ New category</a></x-slot:actions>
    </x-admin.page-header>
    <x-admin.form-errors />

    <div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @forelse($categories as $category)
            <div class="grid gap-4 border-b border-slate-100 px-6 py-5 last:border-0 sm:grid-cols-[1fr_auto] sm:items-center sm:px-8">
                <div class="flex min-w-0 items-center gap-4">
                    @if($category->image_path)<img class="h-14 w-14 shrink-0 rounded-2xl object-cover" src="{{ Storage::url($category->image_path) }}" alt="">@else<span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-50 font-black text-emerald-700">{{ Str::upper(Str::substr($category->name, 0, 1)) }}</span>@endif
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="truncate font-black text-slate-900">{{ $category->name }}</p><span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $category->is_active ? 'Active' : 'Hidden' }}</span></div><p class="mt-1 text-sm text-slate-500">{{ $category->services_count }} {{ Str::plural('service', $category->services_count) }} · Order {{ $category->sort_order }}</p></div>
                </div>
                <div class="flex items-center gap-2">
                    <a class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category? This is only allowed when it has no services.')">@csrf @method('DELETE')<button class="rounded-xl px-3 py-2 text-sm font-bold text-rose-600 hover:bg-rose-50" type="submit">Delete</button></form>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center"><p class="font-black text-slate-900">No categories yet</p><p class="mt-2 text-sm text-slate-500">Create your first category, then add services inside it.</p><a class="brand-button mt-6 px-5 py-3" style="--brand:#047857" href="{{ route('admin.categories.create') }}">Create category</a></div>
        @endforelse
    </div>
</div>
@endsection

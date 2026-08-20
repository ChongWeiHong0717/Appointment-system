@extends('layouts.admin')
@section('title', 'New service')
@section('header', 'Service catalog')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Services" title="Create service" description="Define what customers can book and how much time it needs." />@if($categories->isEmpty())<div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">Create a category before adding a service. <a class="font-black underline" href="{{ route('admin.categories.create') }}">Create category</a></div>@else<form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('admin.services._form', ['submitLabel' => 'Create service'])</form>@endif</div>
@endsection

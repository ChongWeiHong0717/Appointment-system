@extends('layouts.admin')
@section('title', 'New category')
@section('header', 'Service catalog')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Categories" title="Create category" description="Add a customer-friendly group for related services." /><form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('admin.categories._form', ['submitLabel' => 'Create category'])</form></div>
@endsection

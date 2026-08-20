@extends('layouts.admin')
@section('title', 'Edit category')
@section('header', 'Service catalog')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Categories" title="Edit {{ $category->name }}" description="Update how this category appears to customers." /><form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.categories._form', ['submitLabel' => 'Save changes'])</form></div>
@endsection

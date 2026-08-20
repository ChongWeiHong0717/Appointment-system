@extends('layouts.admin')
@section('title', 'Edit service')
@section('header', 'Service catalog')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Services" title="Edit {{ $service->name }}" description="Update booking details and public visibility." /><form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.services.update', $service) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') @include('admin.services._form', ['submitLabel' => 'Save changes'])</form></div>
@endsection

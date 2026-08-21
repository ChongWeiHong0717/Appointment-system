@extends('layouts.admin')
@section('title', 'Edit worker')
@section('header', 'Workers')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Staff capacity" title="Edit {{ $worker->name }}" description="Update availability status and service qualifications." /><form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.workers.update', $worker) }}" method="POST">@csrf @method('PUT') @include('admin.workers._form', ['submitLabel' => 'Save changes'])</form></div>
@endsection

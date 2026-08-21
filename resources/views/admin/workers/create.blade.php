@extends('layouts.admin')
@section('title', 'New worker')
@section('header', 'Workers')
@section('content')
<div class="mx-auto max-w-3xl"><x-admin.page-header eyebrow="Staff capacity" title="Add worker" description="Add a bookable worker and choose which services they can perform." /><form class="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" action="{{ route('admin.workers.store') }}" method="POST">@csrf @include('admin.workers._form', ['submitLabel' => 'Add worker'])</form></div>
@endsection

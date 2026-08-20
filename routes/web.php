<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'create'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/admin/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'business.user'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
});

Route::prefix('{business:slug}')->scopeBindings()->group(function () {
    Route::get('/', [PublicSiteController::class, 'show'])->name('public.home');
    Route::get('/book', [PublicBookingController::class, 'create'])->name('public.booking.create');
    Route::get('/book/slots', [PublicBookingController::class, 'slots'])->name('public.booking.slots');
    Route::post('/book', [PublicBookingController::class, 'store'])->name('public.booking.store');
    Route::get('/book/confirmation/{appointment}', [PublicBookingController::class, 'confirmation'])
        ->middleware('signed')
        ->name('public.booking.confirmation');
});

<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentStatusController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CheckInController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SpecialDateController;
use App\Http\Controllers\Admin\WebsiteController;
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
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('services', ServiceController::class)->except('show');

    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::get('appointments/slots', [AppointmentController::class, 'slots'])->name('appointments.slots');
    Route::post('appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::post('appointments/{appointment}/check-in', [AppointmentStatusController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('appointments/{appointment}/complete', [AppointmentStatusController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{appointment}/cancel', [AppointmentStatusController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/no-show', [AppointmentStatusController::class, 'noShow'])->name('appointments.no-show');

    Route::get('check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::post('check-in/{appointment}', [CheckInController::class, 'store'])->name('check-in.store');

    Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index');
    Route::put('availability/hours', [AvailabilityController::class, 'update'])->name('availability.hours.update');
    Route::post('availability/special-dates', [SpecialDateController::class, 'store'])->name('availability.special-dates.store');
    Route::put('availability/special-dates/{specialDate}', [SpecialDateController::class, 'update'])->name('availability.special-dates.update');
    Route::delete('availability/special-dates/{specialDate}', [SpecialDateController::class, 'destroy'])->name('availability.special-dates.destroy');

    Route::get('website/business', [WebsiteController::class, 'editBusiness'])->name('website.business.edit');
    Route::put('website/business', [WebsiteController::class, 'updateBusiness'])->name('website.business.update');
    Route::get('website/homepage', [WebsiteController::class, 'editHomepage'])->name('website.homepage.edit');
    Route::put('website/homepage', [WebsiteController::class, 'updateHomepage'])->name('website.homepage.update');
    Route::get('website/appearance', [WebsiteController::class, 'editAppearance'])->name('website.appearance.edit');
    Route::put('website/appearance', [WebsiteController::class, 'updateAppearance'])->name('website.appearance.update');
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

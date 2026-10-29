<?php

use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\CustomerController as AdminCustomers;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\GalleryImageController;
use App\Http\Controllers\Admin\SettingsController as AdminSettings;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', HomeController::class)->name('home');
Route::view('/privacy-policy', 'policies.privacy')->name('policies.privacy');
Route::view('/cookie-policy', 'policies.cookies')->name('policies.cookies');
Route::view('/terms', 'policies.terms')->name('policies.terms');
Route::view('/refunds-and-cancellations', 'policies.refunds')->name('policies.refunds');
Route::view('/veterinary-disclaimer', 'policies.veterinary')->name('policies.veterinary');

Route::middleware('guest')->group(function () {
    Route::get('/auth', [AuthController::class, 'show'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.login');
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('auth.register');
    Route::get('/password/forgot', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/password/forgot', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Legacy signed email confirmation links
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Customer area (signed in)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/my-pets', [PetController::class, 'index'])->name('pets.index');
    Route::post('/my-pets', [PetController::class, 'store'])->name('pets.store');
    Route::get('/my-pets/{pet}/edit', [PetController::class, 'edit'])->name('pets.edit');
    Route::put('/my-pets/{pet}', [PetController::class, 'update'])->name('pets.update');
    Route::delete('/my-pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

    Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/book', [BookingController::class, 'store'])->name('booking.store');

    Route::get('/my-bookings', [AppointmentController::class, 'index'])->name('my-bookings');
    Route::post('/my-bookings/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/my-bookings/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::put('/my-bookings/{appointment}/reschedule', [AppointmentController::class, 'updateSchedule'])->name('appointments.update-schedule');
});

/*
|--------------------------------------------------------------------------
| Admin (separate session guard; unauthorized sessions are hidden)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuth::class, 'show'])->name('login');
    Route::post('/login', [AdminAuth::class, 'store'])->middleware('throttle:6,1')->name('login.store');
    Route::get('/email/verify/{id}/{hash}', [AdminSettings::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('email.verify');

    Route::middleware('admin')->group(function () {
        Route::post('/logout', [AdminAuth::class, 'destroy'])->name('logout');
        Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
        Route::post('/appointments/{appointment}/{action}', [AdminDashboard::class, 'transition'])
            ->where('action', 'confirm|complete|no_show|cancel')
            ->name('appointments.transition');

        Route::get('/customers', [AdminCustomers::class, 'index'])->name('customers.index');
        Route::get('/settings', [AdminSettings::class, 'redirectLegacySettings'])->name('settings');
        Route::get('/services', [AdminSettings::class, 'services'])->name('services.index');
        Route::get('/opening-hours', [AdminSettings::class, 'hours'])->name('hours.index');
        Route::get('/account', [AdminSettings::class, 'account'])->name('account.index');
        Route::put('/account/profile', [AdminSettings::class, 'updateProfile'])
            ->middleware('throttle:6,1')
            ->name('profile.update');
        Route::put('/account/password', [AdminSettings::class, 'updatePassword'])
            ->middleware('throttle:6,1')
            ->name('password.update');
        Route::get('/gallery', [GalleryImageController::class, 'index'])->name('gallery.index');
        Route::post('/gallery', [GalleryImageController::class, 'store'])->name('gallery.store');
        Route::delete('/gallery/{galleryImage}', [GalleryImageController::class, 'destroy'])->name('gallery.destroy');
        Route::post('/services', [AdminSettings::class, 'storeService'])->name('services.store');
        Route::put('/services/{service}', [AdminSettings::class, 'updateService'])->name('services.update');
        Route::post('/services/{service}/toggle', [AdminSettings::class, 'toggleService'])->name('services.toggle');
        Route::put('/opening-hours', [AdminSettings::class, 'updateHours'])->name('hours.update');
        Route::post('/opening-hours/exceptions', [AdminSettings::class, 'storeException'])->name('hours.exceptions.store');
        Route::delete('/opening-hours/exceptions/{exception}', [AdminSettings::class, 'destroyException'])->name('hours.exceptions.destroy');
    });
});

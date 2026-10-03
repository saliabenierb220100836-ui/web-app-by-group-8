<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Member;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

// Public: anyone (even logged-out visitors) can see current promos.
Route::get('/promos', [PromoController::class, 'index'])->name('promos');

/*
|--------------------------------------------------------------------------
| Member area
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('member')->group(function () {
        Route::get('/dashboard', Member\DashboardController::class)->name('dashboard');

        Route::get('/pcs/{computer}/book', [Member\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/pcs/{computer}/book', [Member\BookingController::class, 'store'])->middleware('throttle:10,1')->name('bookings.store');
        Route::get('/bookings', [Member\BookingController::class, 'index'])->name('bookings.index');
        Route::post('/bookings/{booking}/cancel', [Member\BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::get('/payments', [Member\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [Member\PaymentController::class, 'store'])->middleware('throttle:10,1')->name('payments.store');

        Route::get('/chat', [Member\ChatController::class, 'index'])->name('chat.index');
        Route::get('/chat/messages', [Member\ChatController::class, 'messages'])->middleware('throttle:90,1')->name('chat.messages');
        Route::post('/chat/messages', [Member\ChatController::class, 'send'])->middleware('throttle:30,1')->name('chat.send');
    });
});

/*
|--------------------------------------------------------------------------
| Admin panel (auth + active account + admin role; everyone else gets a 404)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'active', 'admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('computers', Admin\ComputerController::class)->except('show');

    Route::resource('members', Admin\MemberController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::get('bookings', [Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::post('bookings/{booking}/approve', [Admin\BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('bookings/{booking}/reject', [Admin\BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('bookings/{booking}/check-in', [Admin\BookingController::class, 'checkIn'])->name('bookings.check-in');

    Route::get('attendance', [Admin\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/check-in', [Admin\AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('attendance/{pcSession}/check-out', [Admin\AttendanceController::class, 'checkOut'])->name('attendance.check-out');

    Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/confirm', [Admin\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('payments/{payment}/reject', [Admin\PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('payments/{payment}/proof', [Admin\PaymentController::class, 'proof'])->name('payments.proof');

    Route::get('pricing', [Admin\PricingController::class, 'edit'])->name('pricing.edit');
    Route::put('pricing', [Admin\PricingController::class, 'update'])->name('pricing.update');

    Route::resource('payment-methods', Admin\PaymentMethodController::class)->except('show')->parameters(['payment-methods' => 'paymentMethod']);

    Route::resource('promos', Admin\PromoController::class)->except('show');
});

require __DIR__.'/auth.php';

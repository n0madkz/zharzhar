<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/admin', [AdminController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');
Route::post('/admin/restaurants', [AdminController::class, 'storeRestaurant'])->middleware(['auth', 'role:admin'])->name('admin.restaurants.store');
Route::get('/restaurant', [RestaurantController::class, 'index'])->middleware(['auth', 'role:partner'])->name('restaurant.dashboard');
Route::get('/restaurant/calendar-data', [RestaurantController::class, 'calendarData'])->middleware(['auth', 'role:partner'])->name('restaurant.calendar.data');
Route::get('/restaurant/reports/export', [RestaurantController::class, 'exportReports'])->middleware(['auth', 'role:partner'])->name('restaurant.reports.export');
Route::post('/restaurant/bookings', [RestaurantController::class, 'store'])->middleware(['auth', 'role:partner'])->name('restaurant.bookings.store');
Route::put('/restaurant/bookings/{booking}', [RestaurantController::class, 'updateBooking'])->middleware(['auth', 'role:partner'])->name('restaurant.bookings.update');
Route::delete('/restaurant/bookings/{booking}', [RestaurantController::class, 'destroyBooking'])->middleware(['auth', 'role:partner'])->name('restaurant.bookings.destroy');
Route::get('/restaurant/bookings/{booking}/qr', [RestaurantController::class, 'qr'])->middleware(['auth', 'role:partner'])->name('restaurant.bookings.qr');
Route::put('/restaurant/settings/slots', [RestaurantController::class, 'updateSlots'])->middleware(['auth', 'role:partner'])->name('restaurant.settings.slots');
Route::get('/dashboard', function () {
    return redirect(auth()->user()?->isRole('partner') ? '/restaurant' : '/admin');
})->middleware('auth');

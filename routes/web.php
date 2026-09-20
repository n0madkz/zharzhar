<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\StoreAdminController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\SetPartnerLocale;
use Illuminate\Support\Facades\Route;

Route::post('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['kk', 'ru'], true), 404);
    session(['store_locale' => $locale]);

    return back();
})->name('store.language');

Route::domain(config('store.admin_domain'))->get('/', fn () => redirect('/admin/store'));
Route::domain(config('store.broker_domain'))->get('/', fn () => redirect('/broker'));
Route::middleware(['auth', 'role:broker,admin'])->prefix('broker')->name('broker.')->group(function () {
    Route::get('/', [BrokerController::class, 'index'])->name('index');
    Route::post('/settings', [BrokerController::class, 'settings'])->name('settings');
    Route::post('/import', [BrokerController::class, 'import'])->middleware('throttle:5,1')->name('import');
    Route::post('/collect', [BrokerController::class, 'collect'])->middleware('throttle:2,60')->name('collect');
    Route::post('/venues/{venue}/complete', [BrokerController::class, 'complete'])->name('complete');
    Route::post('/venues/{venue}/register', [BrokerController::class, 'register'])->middleware('throttle:10,1')->name('register');
});
Route::get('/admin/brokers', [BrokerController::class, 'staff'])->middleware(['auth', 'role:admin'])->name('admin.brokers');
Route::post('/admin/brokers', [BrokerController::class, 'createStaff'])->middleware(['auth', 'role:admin'])->name('admin.brokers.store');
Route::domain(config('store.partner_domain'))->get('/', fn () => redirect(auth()->user()?->isRole('partner') ? '/restaurant' : '/login'))->name('partner.home');
Route::get('/', [StorefrontController::class, 'index'])->name('store.catalog');
Route::get('/designs/{template}/preview', [StorefrontController::class, 'preview'])->name('store.preview');
Route::get('/checkout/{template}', [StorefrontController::class, 'checkout'])->name('store.checkout');
Route::post('/checkout', [StorefrontController::class, 'store'])->middleware('throttle:10,1')->name('store.order');
Route::post('/checkout/quote', [StorefrontController::class, 'quote'])->middleware('throttle:30,1')->name('store.quote');
Route::get('/media/music/{filename}', [StorefrontController::class, 'musicFile'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('store.music');
Route::get('/media/invitation-photo/{filename}', [StorefrontController::class, 'photoFile'])
    ->where('filename', '[A-Za-z0-9._-]+')
    ->name('store.photo');
Route::get('/orders/{token}', [StorefrontController::class, 'payment'])->name('store.payment');
Route::post('/orders/{token}/payment', [StorefrontController::class, 'submitPayment'])->middleware('throttle:5,1')->name('store.payment.submit');
Route::get('/i/{slug}', [StorefrontController::class, 'invitation'])->name('store.invitation');
Route::post('/i/{slug}/rsvp', [StorefrontController::class, 'rsvp'])->middleware('throttle:10,1')->name('store.rsvp');
Route::get('/responses/{token}', [StorefrontController::class, 'responses'])->name('store.responses');
Route::middleware(['auth', 'role:admin'])->prefix('admin/store')->name('admin.store.')->group(function () {
    Route::get('/', [StoreAdminController::class, 'index'])->name('index');
    Route::get('/orders/{order}/edit', [StoreAdminController::class, 'editOrder'])->name('orders.edit');
    Route::put('/orders/{order}', [StoreAdminController::class, 'updateOrder'])->name('orders.update');
    Route::delete('/orders/{order}', [StoreAdminController::class, 'destroyOrder'])->name('orders.destroy');
    Route::post('/orders/{order}/confirm', [StoreAdminController::class, 'confirm'])->name('confirm');
    Route::post('/orders/{order}/reject', [StoreAdminController::class, 'reject'])->name('reject');
    Route::post('/orders/{order}/archive', [StoreAdminController::class, 'archiveInvitation'])->name('orders.archive');
    Route::post('/templates/{template?}', [StoreAdminController::class, 'saveTemplate'])->name('templates');
    Route::post('/music/{music?}', [StoreAdminController::class, 'saveMusic'])->name('music');
    Route::post('/promos/{promo?}', [StoreAdminController::class, 'savePromo'])->name('promos');
    Route::post('/restaurants/{restaurant?}', [StoreAdminController::class, 'saveRestaurant'])->name('restaurants');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/admin', [AdminController::class, 'index'])->middleware(['auth', 'role:admin'])->name('admin.dashboard');
Route::post('/admin/restaurants', [AdminController::class, 'storeRestaurant'])->middleware(['auth', 'role:admin'])->name('admin.restaurants.store');
Route::put('/admin/restaurants/{restaurant}', [AdminController::class, 'updateRestaurant'])->middleware(['auth', 'role:admin'])->name('admin.restaurants.update');
Route::get('/admin/restaurants/{restaurant}/invitation-card', [AdminController::class, 'restaurantInvitationCard'])->middleware(['auth', 'role:admin'])->name('admin.restaurants.invitation-card');
Route::get('/admin/bookings/pdf', [AdminController::class, 'bookingsPdf'])->middleware(['auth', 'role:admin'])->name('admin.bookings.pdf');
Route::get('/admin/bookings/{booking}', [AdminController::class, 'showBooking'])->middleware(['auth', 'role:admin'])->name('admin.bookings.show');
Route::post('/admin/payouts/{payout}/pay', [AdminController::class, 'payPayout'])->middleware(['auth', 'role:admin'])->name('admin.payouts.pay');
Route::post('/admin/payouts/{payout}/reject', [AdminController::class, 'rejectPayout'])->middleware(['auth', 'role:admin'])->name('admin.payouts.reject');
Route::domain(config('store.partner_domain'))->middleware(['partner.domain', 'auth', 'role:partner', SetPartnerLocale::class])->group(function () {
    Route::get('/restaurant', [RestaurantController::class, 'index'])->name('restaurant.dashboard');
    Route::get('/restaurant/calendar-data', [RestaurantController::class, 'calendarData'])->name('restaurant.calendar.data');
    Route::get('/restaurant/reports/export', [RestaurantController::class, 'exportReports'])->name('restaurant.reports.export');
    Route::get('/restaurant/invitation-card', [RestaurantController::class, 'invitationCard'])->name('restaurant.invitation-card');
    Route::post('/restaurant/bonuses/payout', [RestaurantController::class, 'requestPayout'])->name('restaurant.bonuses.payout');
    Route::post('/restaurant/bookings', [RestaurantController::class, 'store'])->name('restaurant.bookings.store');
    Route::put('/restaurant/bookings/{booking}', [RestaurantController::class, 'updateBooking'])->name('restaurant.bookings.update');
    Route::delete('/restaurant/bookings/{booking}', [RestaurantController::class, 'destroyBooking'])->name('restaurant.bookings.destroy');
    Route::get('/restaurant/bookings/{booking}/qr', [RestaurantController::class, 'qr'])->name('restaurant.bookings.qr');
    Route::put('/restaurant/settings/slots', [RestaurantController::class, 'updateSlots'])->name('restaurant.settings.slots');
    Route::put('/restaurant/settings/language', [RestaurantController::class, 'updateLanguage'])->name('restaurant.settings.language');
    Route::post('/restaurant/settings/packages', [RestaurantController::class, 'storePackage'])->name('restaurant.packages.store');
    Route::put('/restaurant/settings/packages/{tariff}', [RestaurantController::class, 'updatePackage'])->name('restaurant.packages.update');
    Route::delete('/restaurant/settings/packages/{tariff}', [RestaurantController::class, 'destroyPackage'])->name('restaurant.packages.destroy');
});
Route::get('/dashboard', function () {
    return redirect(auth()->user()?->isRole('broker') ? '/broker' : (auth()->user()?->isRole('partner') ? '/restaurant' : '/admin'));
})->middleware('auth');

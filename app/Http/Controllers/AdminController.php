<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Restaurant;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['restaurant', 'slot'])->latest('booking_date')->latest()->get();
        $restaurants = Restaurant::withCount('bookings')->latest()->get();
        return view('admin.dashboard', compact('bookings', 'restaurants'));
    }

    public function storeRestaurant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'max_seats' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
        $partner = \App\Models\User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'role' => 'partner', 'password' => Hash::make($data['password'])]);
        $restaurant = Restaurant::create([...$data, 'partner_user_id' => $partner->id]);
        foreach ([['morning', 'Утро', '09:00', '12:00', '#6fa982'], ['day', 'День', '13:00', '17:00', '#d8b36a'], ['evening', 'Вечер', '18:00', '22:00', '#c46b58']] as [$key, $label, $start, $end, $color]) {
            $restaurant->slots()->create(['slot_key' => $key, 'label' => $label, 'start_time' => $start, 'end_time' => $end, 'color' => $color]);
        }
        return back()->with('success', 'Ресторан добавлен. Данные для входа сохранены.');
    }
}

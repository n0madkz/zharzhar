<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['restaurant', 'slot'])->latest('booking_date')->latest()->get();
        $restaurants = Restaurant::with('partner')->withCount('bookings')->latest()->get();

        return view('admin.dashboard-v2', compact('bookings', 'restaurants'));
    }

    public function storeRestaurant(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'two_gis_url' => ['nullable', 'url:http,https', 'max:500'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'max_seats' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'bonus_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($data): void {
            $partner = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'role' => 'partner', 'password' => Hash::make($data['password'])]);
            $restaurant = Restaurant::create([
                'name' => $data['name'], 'city' => $data['city'], 'address' => $data['address'] ?? null,
                'two_gis_url' => $data['two_gis_url'] ?? null, 'phone' => $data['phone'],
                'max_seats' => $data['max_seats'] ?? null, 'bonus_percent' => $data['bonus_percent'] ?? 0, 'partner_user_id' => $partner->id,
            ]);
            foreach ([['morning', 'Утро', '09:00', '12:00', '#6fa982'], ['day', 'День', '13:00', '17:00', '#d8b36a'], ['evening', 'Вечер', '18:00', '22:00', '#c46b58']] as [$key, $label, $start, $end, $color]) {
                $restaurant->slots()->create(['slot_key' => $key, 'label' => $label, 'start_time' => $start, 'end_time' => $end, 'color' => $color]);
            }
        });

        return back()->with('success', 'Ресторан добавлен. Данные для входа сохранены.');
    }

    public function updateRestaurant(Request $request, Restaurant $restaurant): RedirectResponse
    {
        $partner = $restaurant->partner;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'two_gis_url' => ['nullable', 'url:http,https', 'max:500'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($partner?->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'max_seats' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'bonus_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        if (! $partner && empty($data['password'])) {
            throw ValidationException::withMessages(['password' => 'Для ресторана без аккаунта укажите новый пароль.']);
        }
        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($data, $restaurant, $partner, $isActive): void {
            $restaurant->update([
                'name' => $data['name'], 'city' => $data['city'], 'address' => $data['address'] ?? null,
                'two_gis_url' => $data['two_gis_url'] ?? null, 'phone' => $data['phone'],
                'max_seats' => $data['max_seats'] ?? null, 'bonus_percent' => $data['bonus_percent'] ?? 0, 'status' => $isActive ? 'active' : 'inactive',
            ]);
            $account = $partner ?? new User(['role' => 'partner']);
            $account->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']]);
            if (! empty($data['password'])) {
                $account->password = Hash::make($data['password']);
            }
            $account->save();
            if (! $restaurant->partner_user_id) {
                $restaurant->update(['partner_user_id' => $account->id]);
            }
        });

        return back()->with('success', 'Данные ресторана и аккаунт партнёра обновлены.');
    }

    public function showBooking(Booking $booking): View
    {
        $booking->load(['restaurant', 'slot']);

        return view('admin.booking-show', compact('booking'));
    }

    public function bookingPdf(Booking $booking): Response
    {
        $booking->load(['restaurant', 'slot']);
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('admin.booking-pdf', compact('booking'))->render(), 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="zharzhar-booking-'.$booking->id.'.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}

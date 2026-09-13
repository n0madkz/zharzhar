<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantSlot;
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
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
            'restaurant' => ['nullable', 'string', 'max:150'],
            'period' => ['nullable', 'string', 'max:50'],
            'event_type' => ['nullable', 'string', 'max:120'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $search = trim($filters['q'] ?? '');
        $phoneSearch = preg_replace('/\D+/', '', $search);
        $bookings = Booking::with(['restaurant', 'slot', 'tariff.service'])
            ->when($search !== '', function ($query) use ($search, $phoneSearch): void {
                $query->where(function ($query) use ($search, $phoneSearch): void {
                    $query->where('visitor_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                    if ($phoneSearch !== '') {
                        $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", ['%'.$phoneSearch.'%']);
                    }
                });
            })
            ->when(! empty($filters['restaurant_id']), fn ($query) => $query->where('restaurant_id', $filters['restaurant_id']))
            ->when(($filters['restaurant'] ?? '') !== '', function ($query) use ($filters): void {
                $restaurantSearch = trim($filters['restaurant']);
                $query->whereHas('restaurant', fn ($restaurantQuery) => $restaurantQuery
                    ->where('name', 'like', '%'.$restaurantSearch.'%')
                    ->orWhere('city', 'like', '%'.$restaurantSearch.'%'));
            })
            ->when(($filters['period'] ?? '') !== '', fn ($query) => $query->whereHas('slot', fn ($slotQuery) => $slotQuery->where('slot_key', $filters['period'])))
            ->when(($filters['event_type'] ?? '') !== '', fn ($query) => $query->where('event_type', 'like', '%'.$filters['event_type'].'%'))
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('booking_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('booking_date', '<=', $filters['date_to']))
            ->latest('booking_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $restaurants = Restaurant::with('partner')->withCount('bookings')->latest()->get();
        $periods = RestaurantSlot::query()->orderBy('start_time')->get()->unique('slot_key')->values();
        $eventTypes = Booking::query()->whereNotNull('event_type')->where('event_type', '!=', '')->distinct()->orderBy('event_type')->pluck('event_type');

        return view('admin.dashboard-v2', compact('bookings', 'restaurants', 'periods', 'eventTypes', 'filters', 'search'));
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
        $booking->load(['restaurant', 'slot', 'tariff.service']);

        return view('admin.booking-show', compact('booking'));
    }

    public function bookingsPdf(): Response
    {
        $bookings = Booking::with(['restaurant', 'slot', 'tariff.service'])
            ->orderBy('booking_date')
            ->orderBy('id')
            ->get();
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('admin.bookings-pdf', compact('bookings'))->render(), 'UTF-8');
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="zharzhar-all-bookings.pdf"',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\CollectBrokerVenues;
use App\Models\BrokerVenue;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\BrokerDirectory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BrokerController extends Controller
{
    public function index(Request $request, BrokerDirectory $directory): View
    {
        $data = $request->validate(['city' => ['nullable', 'string', 'max:100']]);
        $cities = BrokerVenue::query()->distinct()->orderBy('city')->pluck('city')->merge(array_keys(config('broker.cities')))->unique()->sort()->values();
        $city = $data['city'] ?? $request->user()->broker_city ?? 'Атырау';
        $restaurants = Restaurant::with('partner')->get();
        $venues = BrokerVenue::where('city', $city)->orderBy('name')->get()->map(function ($venue) use ($directory, $restaurants) {
            $restaurant = $directory->match($venue, $restaurants);

            return [
                'id' => $venue->id, 'name' => $venue->name, 'district' => $venue->district ?: 'Район не указан',
                'address' => $venue->address, 'phone' => $venue->phone, 'email' => $venue->email,
                'lat' => $venue->latitude, 'lng' => $venue->longitude, 'url' => $venue->two_gis_url,
                'completed' => (bool) $venue->completed_at,
                'partner' => $restaurant?->partner ? [
                    'name' => $restaurant->name, 'email' => $restaurant->partner->email,
                    'phone' => $restaurant->phone, 'status' => $restaurant->status,
                ] : null,
            ];
        });

        return view('broker.index', compact('cities', 'city', 'venues'));
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', 'string', 'max:100']]);
        $request->user()->forceFill(['broker_city' => $data['city']])->save();

        return redirect('/broker')->with('success', 'Город сохранён.');
    }

    public function collect(Request $request): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', Rule::in(array_keys(config('broker.cities')))]]);
        if (! config('broker.parser_python')) {
            throw ValidationException::withMessages(['city' => 'Автосбор пока не настроен на сервере. Используйте загрузку JSON или обратитесь к администратору.']);
        }
        CollectBrokerVenues::dispatch($data['city']);

        return back()->with('success', 'Запрос сбора отправлен. Он выполнится, когда запущен обработчик парсера.');
    }

    public function import(Request $request, BrokerDirectory $directory): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:20480'],
        ]);
        $count = $directory->import(file_get_contents($request->file('file')->getRealPath()), $data['city']);
        $request->user()->forceFill(['broker_city' => $data['city']])->save();

        return redirect('/broker')->with('success', "Загружено записей: {$count}. Повторные записи обновлены.");
    }

    public function complete(Request $request, BrokerVenue $venue): JsonResponse
    {
        $request->validate(['completed' => ['required', 'boolean']]);
        $venue->update([
            'completed_at' => $request->boolean('completed') ? now() : null,
            'completed_by' => $request->boolean('completed') ? $request->user()->id : null,
        ]);

        return response()->json(['completed' => (bool) $venue->completed_at]);
    }

    public function register(Request $request, BrokerVenue $venue, BrokerDirectory $directory): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[+\d\s()\-]{10,30}$/'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);
        $phone = BrokerDirectory::phone($data['phone']);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            throw ValidationException::withMessages(['phone' => 'Укажите полный номер телефона.']);
        }
        DB::transaction(function () use ($venue, $data, $phone, $directory, $request) {
            // Lock the prospect so double clicks and two employees cannot create two accounts.
            $locked = BrokerVenue::whereKey($venue->id)->lockForUpdate()->firstOrFail();
            $restaurant = $directory->match($locked, Restaurant::with('partner')->get());
            if ($restaurant?->partner_user_id) {
                throw ValidationException::withMessages(['email' => 'Этот зал уже подключён к партнёрам. Обновите список.']);
            }
            $user = User::create([
                'name' => $locked->name, 'email' => $data['email'], 'phone' => '+'.$phone,
                'password' => $data['password'], 'role' => 'partner', 'status' => 'active',
            ]);
            // A pre-existing catalog restaurant without an account is connected in place.
            $restaurant ??= new Restaurant([
                'name' => $locked->name, 'city' => $locked->city, 'address' => $locked->address,
                'two_gis_url' => $locked->two_gis_url,
            ]);
            $restaurant->fill(['partner_user_id' => $user->id, 'phone' => '+'.$phone])->save();
            foreach ([['morning', 'Утро', '09:00', '12:00'], ['day', 'День', '13:00', '17:00'], ['evening', 'Вечер', '18:00', '22:00']] as [$key, $label, $start, $end]) {
                $restaurant->slots()->firstOrCreate(['slot_key' => $key], ['label' => $label, 'start_time' => $start, 'end_time' => $end, 'color' => '#2563eb']);
            }
            $locked->update(['restaurant_id' => $restaurant->id, 'completed_at' => now(), 'completed_by' => $request->user()->id]);
        });

        return redirect('/broker?'.http_build_query(['city' => $venue->city]))->with('success', 'Ресторан подключён. Вход партнёра: https://'.config('store.partner_domain').'/login. Передайте ресторану указанные email и пароль.');
    }

    public function staff(): View
    {
        $staff = User::where('role', 'broker')->orderBy('name')->paginate(10);

        return view('broker.staff', compact('staff'));
    }

    public function createStaff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ]);
        User::create([...$data, 'role' => 'broker', 'status' => 'active']);

        return back()->with('success', 'Сотрудник добавлен.');
    }
}

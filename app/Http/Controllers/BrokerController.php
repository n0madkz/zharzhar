<?php

namespace App\Http\Controllers;

use App\Models\BrokerVenue;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\BrokerDirectory;
use App\Support\BrokerParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BrokerController extends Controller
{
    public function index(Request $request, BrokerDirectory $directory): View
    {
        $data = $request->validate(['city' => ['nullable', 'string', 'max:100']]);
        $cities = $this->assignedCities($request->user());
        $city = $data['city'] ?? $request->user()->broker_city ?? $cities->first();
        abort_unless($cities->contains($city), 403);
        $restaurants = Restaurant::with('partner')->get();
        $venues = BrokerVenue::where('city', $city)->orderBy('name')->get()->map(function ($venue) use ($directory, $restaurants) {
            $restaurant = $directory->match($venue, $restaurants);

            return [
                'id' => $venue->id, 'name' => $venue->name, 'district' => $venue->district ?: 'Район не указан',
                'address' => $venue->address, 'phone' => $venue->phone, 'email' => $venue->email,
                'lat' => $venue->latitude, 'lng' => $venue->longitude, 'url' => $venue->two_gis_url,
                'dealStatus' => $venue->deal_status ?: ($venue->completed_at ? 'closed' : 'open'),
                'partner' => $restaurant?->partner ? [
                    'name' => $restaurant->name, 'email' => $restaurant->partner->email,
                    'phone' => $restaurant->phone, 'status' => $restaurant->status,
                ] : null,
            ];
        });

        $availableCities = collect(array_keys(config('broker.cities')))->diff($cities)->values();

        $parserReady = is_file((string) config('broker.parser_python'));

        return view('broker.index', compact('cities', 'availableCities', 'city', 'venues', 'parserReady'));
    }

    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', Rule::in($this->assignedCities($request->user())->all())]]);
        $request->user()->forceFill(['broker_city' => $data['city']])->save();

        return redirect('/broker')->with('success', 'Город сохранён.');
    }

    public function addCity(Request $request, BrokerParser $parser): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', Rule::in(array_keys(config('broker.cities')))]]);
        $cities = $this->assignedCities($request->user());
        if (! $cities->contains($data['city'])) {
            $cities->push($data['city']);
        }
        $request->user()->forceFill([
            'broker_city' => $data['city'],
            'broker_cities' => $cities->unique()->values()->all(),
        ])->save();
        $count = $parser->collect($data['city']);

        return redirect('/broker?'.http_build_query(['city' => $data['city']]))
            ->with('success', "Город добавлен. Из 2GIS загружено залов: {$count}.");
    }

    public function collect(Request $request, BrokerParser $parser): RedirectResponse
    {
        $data = $request->validate(['city' => ['required', Rule::in($this->assignedCities($request->user())->all())]]);
        $count = $parser->collect($data['city']);

        return redirect('/broker?'.http_build_query(['city' => $data['city']]))
            ->with('success', "Данные 2GIS обновлены. Загружено залов: {$count}.");
    }

    public function complete(Request $request, BrokerVenue $venue): JsonResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'closed', 'failed'])]]);
        $closed = $data['status'] === 'closed';
        $venue->update([
            'deal_status' => $data['status'],
            'completed_at' => $closed ? ($venue->completed_at ?: now()) : null,
            'completed_by' => $data['status'] === 'open' ? null : $request->user()->id,
        ]);

        return response()->json(['status' => $venue->deal_status]);
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
            $locked->update(['restaurant_id' => $restaurant->id, 'deal_status' => 'closed', 'completed_at' => now(), 'completed_by' => $request->user()->id]);
        });

        return redirect('/broker?'.http_build_query(['city' => $venue->city]))->with('success', 'Ресторан подключён. Вход партнёра: https://'.config('store.partner_domain').'/login. Передайте ресторану указанные email и пароль.');
    }

    public function staff(): View
    {
        $staff = User::where('role', 'broker')->orderBy('name')->paginate(10);
        $cities = array_keys(config('broker.cities'));

        return view('broker.staff', compact('staff', 'cities'));
    }

    public function createStaff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => ['required', Rule::in(array_keys(config('broker.cities')))],
        ]);
        $cities = array_values(array_unique($data['cities']));
        User::create([
            'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
            'role' => 'broker', 'status' => 'active', 'broker_city' => $cities[0], 'broker_cities' => $cities,
        ]);

        return back()->with('success', 'Сотрудник добавлен.');
    }

    public function updateStaff(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isRole('broker'), 404);
        $data = $request->validate([
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => ['required', Rule::in(array_keys(config('broker.cities')))],
        ]);
        $cities = array_values(array_unique($data['cities']));
        $user->update(['broker_city' => $cities[0], 'broker_cities' => $cities]);

        return back()->with('success', 'Города сотрудника обновлены.');
    }

    private function assignedCities(User $user): Collection
    {
        if ($user->isRole('admin')) {
            return collect(array_keys(config('broker.cities')));
        }

        return collect($user->broker_cities ?? [])
            ->when($user->broker_city, fn ($cities) => $cities->prepend($user->broker_city))
            ->filter(fn ($city) => array_key_exists($city, config('broker.cities')))
            ->unique()->values()
            ->whenEmpty(fn ($cities) => $cities->push('Атырау'));
    }
}

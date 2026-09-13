<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Restaurant;
use App\Models\RestaurantService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@zharzhar.local'], ['name' => 'Главный администратор', 'role' => 'admin', 'password' => Hash::make('Gamepro7')]);
        $partner = User::updateOrCreate(['email' => 'restaurant.demo@zharzhar.local'], ['name' => 'Demo Restaurant', 'role' => 'partner', 'password' => Hash::make('Gamepro7')]);
        $restaurant = Restaurant::updateOrCreate(['name' => 'Demo Restaurant'], ['city' => 'Алматы', 'phone' => '+7 700 000 00 00', 'partner_user_id' => $partner->id]);
        foreach ([['morning', 'Утро', '09:00', '12:00', '#6fa982'], ['day', 'День', '13:00', '17:00', '#d8b36a'], ['evening', 'Вечер', '18:00', '22:00', '#c46b58']] as [$key, $label, $start, $end, $color]) {
            $restaurant->slots()->updateOrCreate(['slot_key' => $key], ['label' => $label, 'start_time' => $start, 'end_time' => $end, 'color' => $color]);
        }
        $banquet = RestaurantService::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Банкет'],
            ['description' => 'Банкетное обслуживание', 'is_active' => true],
        );
        $banquet->tariffs()->updateOrCreate(['name' => 'Стандарт'], ['description' => 'Основное банкетное меню', 'price_per_guest' => 15000, 'is_active' => true]);
        $banquet->tariffs()->updateOrCreate(['name' => 'Премиум'], ['description' => 'Расширенное меню и обслуживание', 'price_per_guest' => 22000, 'is_active' => true]);
    }
}

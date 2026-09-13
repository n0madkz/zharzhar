<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_booking_in_russian_and_can_export_all_bookings_to_one_pdf(): void
    {
        [$admin, $restaurant, $booking] = $this->bookingFixture();

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboard->assertOk()
            ->assertSee('Подтверждено')
            ->assertDontSee('Ожидает подтверждения')
            ->assertSee('https://wa.me/77071234567', false)
            ->assertSee(route('admin.bookings.show', $booking), false)
            ->assertSee(route('admin.bookings.pdf'), false);

        $detail = $this->get(route('admin.bookings.show', $booking));
        $detail->assertOk()
            ->assertSee('Айгуль')
            ->assertSee('+7 707 123 45 67')
            ->assertSee('https://wa.me/77071234567', false)
            ->assertSee('https://2gis.kz/almaty/geo/700000010', false);

        $secondBooking = $booking->replicate(['phone', 'note']);
        $secondBooking->visitor_name = 'Второй гость';
        $secondBooking->booking_date = '2026-10-15';
        $secondBooking->save();

        $pdfTable = view('admin.bookings-pdf', [
            'bookings' => Booking::with(['restaurant', 'slot', 'tariff.service'])->orderBy('id')->get(),
        ])->render();
        $this->assertStringContainsString($booking->visitor_name, $pdfTable);
        $this->assertStringContainsString('Второй гость', $pdfTable);

        $pdf = $this->get(route('admin.bookings.pdf'));
        $pdf->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="zharzhar-all-bookings.pdf"');
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_admin_can_create_restaurant_with_two_gis_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.restaurants.store'), [
            'name' => 'Restaurant Altyn',
            'city' => 'Алматы',
            'address' => 'Абая, 1',
            'two_gis_url' => 'https://2gis.kz/almaty/geo/700000010',
            'phone' => '+7 700 111 22 33',
            'email' => 'partner@example.com',
            'password' => 'SecurePass77',
            'max_seats' => 250,
            'bonus_percent' => 8.5,
        ])->assertRedirect();

        $restaurant = Restaurant::where('name', 'Restaurant Altyn')->firstOrFail();
        $this->assertSame('https://2gis.kz/almaty/geo/700000010', $restaurant->two_gis_url);
        $this->assertSame('partner@example.com', $restaurant->partner?->email);
        $this->assertSame('8.50', $restaurant->bonus_percent);
        $this->assertCount(3, $restaurant->slots);
    }

    public function test_admin_can_filter_bookings_and_search_by_partial_phone_or_name(): void
    {
        [$admin, $restaurant, $booking] = $this->bookingFixture();
        $otherRestaurant = Restaurant::create([
            'name' => 'Aurora',
            'city' => 'Астана',
            'phone' => '+7 700 000 11 22',
        ]);
        $otherSlot = RestaurantSlot::create([
            'restaurant_id' => $otherRestaurant->id,
            'slot_key' => 'morning',
            'label' => 'Утро',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'color' => '#2563eb',
        ]);
        Booking::create([
            'restaurant_id' => $otherRestaurant->id,
            'restaurant_slot_id' => $otherSlot->id,
            'visitor_name' => 'Данияр',
            'event_type' => 'День рождения',
            'phone' => '+7 701 999 88 77',
            'booking_date' => '2026-11-20',
            'guest_count' => 30,
            'price_per_guest' => 12000,
            'prepayment' => 0,
            'status' => 'confirmed',
        ]);

        foreach ([
            ['q' => 'йгу'],
            ['q' => '12345'],
            ['restaurant_id' => $restaurant->id],
            ['restaurant' => 'odem'],
            ['period' => 'evening'],
            ['event_type' => 'вад'],
            ['date_from' => '2026-10-01', 'date_to' => '2026-10-31'],
        ] as $filters) {
            $this->actingAs($admin)
                ->get(route('admin.dashboard', $filters))
                ->assertOk()
                ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 1 && $bookings->first()->is($booking));
        }
    }

    public function test_admin_can_change_restaurant_email_and_reset_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $partner = User::factory()->create(['role' => 'partner', 'email' => 'old@example.com']);
        $restaurant = Restaurant::create([
            'name' => 'Old name',
            'city' => 'Алматы',
            'phone' => '+7 700 000 00 00',
            'partner_user_id' => $partner->id,
        ]);

        $this->actingAs($admin)->put(route('admin.restaurants.update', $restaurant), [
            'name' => 'New name',
            'city' => 'Астана',
            'address' => 'Туран, 10',
            'two_gis_url' => 'https://2gis.kz/astana/geo/700000011',
            'phone' => '+7 701 111 22 33',
            'email' => 'new@example.com',
            'password' => 'NewSecure99',
            'max_seats' => 300,
            'bonus_percent' => 12.25,
            'is_active' => '1',
        ])->assertRedirect();

        $restaurant->refresh();
        $partner->refresh();
        $this->assertSame('New name', $restaurant->name);
        $this->assertSame('https://2gis.kz/astana/geo/700000011', $restaurant->two_gis_url);
        $this->assertSame('12.25', $restaurant->bonus_percent);
        $this->assertSame('new@example.com', $partner->email);
        $this->assertTrue(Hash::check('NewSecure99', $partner->password));
    }

    public function test_non_admin_cannot_open_booking_or_pdf(): void
    {
        [, , $booking] = $this->bookingFixture();
        $partner = User::factory()->create(['role' => 'partner']);

        $this->actingAs($partner)->get(route('admin.bookings.show', $booking))->assertForbidden();
        $this->get(route('admin.bookings.pdf'))->assertForbidden();
    }

    private function bookingFixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = Restaurant::create([
            'name' => 'Rodem',
            'city' => 'Алматы',
            'address' => 'Султан Бейбарыса, 235',
            'two_gis_url' => 'https://2gis.kz/almaty/geo/700000010',
            'phone' => '+7 778 154 32 94',
        ]);
        $slot = RestaurantSlot::create([
            'restaurant_id' => $restaurant->id,
            'slot_key' => 'evening',
            'label' => 'Вечер',
            'start_time' => '18:00',
            'end_time' => '22:00',
            'color' => '#c46b58',
        ]);
        $booking = Booking::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'visitor_name' => 'Айгуль',
            'event_type' => 'Свадьба',
            'phone' => '+7 707 123 45 67',
            'booking_date' => '2026-10-14',
            'guest_count' => 100,
            'price_per_guest' => 15000,
            'prepayment' => 100000,
            'status' => 'pending',
            'note' => 'Нужен проектор',
        ]);

        return [$admin, $restaurant, $booking];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\RestaurantSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_partner_calendar_returns_one_month_without_generating_qr_codes(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();
        Booking::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'visitor_name' => 'Айжан',
            'event_type' => 'Свадьба',
            'phone' => '+7 700 123 45 67',
            'booking_date' => '2026-10-14',
            'guest_count' => 80,
            'price_per_guest' => 15000,
            'prepayment' => 100000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($partner)->getJson('http://partner.zharzhar.kz/restaurant/calendar-data?month=2026-10');

        $response->assertOk()
            ->assertJsonPath('value', '2026-10')
            ->assertJsonPath('bookings.0.date', '2026-10-14')
            ->assertJsonPath('bookings.0.slotId', $slot->id)
            ->assertJsonMissingPath('bookings.0.qr');
        $this->assertSame('2026-09-28', $response->json('days.0.date'));
        $this->assertSame('2026-11-01', $response->json('days.'.(count($response->json('days')) - 1).'.date'));
    }

    public function test_partner_calendar_rejects_invalid_month_and_uses_svg_controls(): void
    {
        [$partner] = $this->partnerFixture();

        $this->actingAs($partner)
            ->getJson('http://partner.zharzhar.kz/restaurant/calendar-data?month=2026-13')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('month');

        $this->get('http://partner.zharzhar.kz/restaurant?month=2026-10&date=2026-10-01')
            ->assertOk()
            ->assertSee('class="month-arrow"', false)
            ->assertSee('data-calendar-today', false)
            ->assertDontSee('data-step="-12"', false)
            ->assertDontSee('data-step="12"', false);
    }

    private function partnerFixture(): array
    {
        $partner = User::factory()->create(['role' => 'partner']);
        $restaurant = Restaurant::create([
            'name' => 'Тестовый ресторан',
            'city' => 'Алматы',
            'address' => 'Абая, 1',
            'phone' => '+7 700 000 00 00',
            'partner_user_id' => $partner->id,
        ]);
        $slot = RestaurantSlot::create([
            'restaurant_id' => $restaurant->id,
            'slot_key' => 'evening',
            'label' => 'Вечер',
            'start_time' => '18:00',
            'end_time' => '23:00',
            'color' => '#c46b58',
        ]);

        return [$partner, $restaurant, $slot];
    }
}

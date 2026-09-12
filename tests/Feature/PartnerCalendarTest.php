<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BonusTransaction;
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

        $this->actingAs($partner)
            ->get('http://partner.zharzhar.kz/restaurant?month=2026-10&date=2026-10-14')
            ->assertOk()
            ->assertDontSee('Написать в WhatsApp')
            ->assertDontSee('booking-support', false)
            ->assertDontSee('</small></span><span class="status">', false);
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

    public function test_partner_dashboard_is_rendered_in_saved_language_without_client_side_translation(): void
    {
        [$partner] = $this->partnerFixture();
        $partner->update(['preferred_language' => 'kk']);

        $response = $this->actingAs($partner)
            ->get('http://partner.zharzhar.kz/restaurant?month=2026-04&date=2026-04-01');

        $response->assertOk()
            ->assertSee('<html lang="kk">', false)
            ->assertSee('Бронь күнтізбесі')
            ->assertSee('сәуір 2026')
            ->assertSee('Қазақша')
            ->assertSee('Русский')
            ->assertSee('English')
            ->assertDontSee('Календарь бронирований');
    }

    public function test_partner_can_save_each_supported_dashboard_language(): void
    {
        [$partner] = $this->partnerFixture();

        foreach (['kk', 'ru', 'en'] as $locale) {
            $this->actingAs($partner)
                ->put('http://partner.zharzhar.kz/restaurant/settings/language', [
                    'preferred_language' => $locale,
                ])
                ->assertRedirect('http://partner.zharzhar.kz/restaurant#settings');

            $this->assertSame($locale, $partner->fresh()->preferred_language);
        }

        $this->get('http://partner.zharzhar.kz/restaurant')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Booking calendar')
            ->assertDontSee('Календарь бронирований');
    }

    public function test_partner_language_rejects_unsupported_values(): void
    {
        [$partner] = $this->partnerFixture();

        $this->actingAs($partner)
            ->from('http://partner.zharzhar.kz/restaurant#settings')
            ->put('http://partner.zharzhar.kz/restaurant/settings/language', [
                'preferred_language' => 'de',
            ])
            ->assertSessionHasErrors('preferred_language');
    }

    public function test_partner_sees_bonus_rate_balance_and_history(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();
        $restaurant->update(['bonus_percent' => 9.5]);
        BonusTransaction::create([
            'restaurant_id' => $restaurant->id,
            'amount' => 949.05,
            'type' => 'accrual',
            'status' => 'available',
            'note' => 'Test accrual',
        ]);

        $this->actingAs($partner)
            ->get('http://partner.zharzhar.kz/restaurant#bonuses')
            ->assertOk()
            ->assertSee('9.5%')
            ->assertSee('949,05 ₸')
            ->assertDontSee('Әзірге бонус есептелмеді.');
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

<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BonusTransaction;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\InvitationOrder;
use App\Models\Restaurant;
use App\Models\RestaurantSlot;
use App\Models\RestaurantService;
use App\Models\RestaurantTariff;
use App\Models\Template;
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
        $template = Template::factory()->create(['name' => 'Алтын салтанат']);
        $event = Event::create([
            'restaurant_id' => $restaurant->id,
            'event_type' => 'wedding',
            'title' => 'Алихан және Аружан',
            'event_date' => '2026-10-14',
            'event_time' => '18:00',
            'language' => 'kk',
            'status' => 'active',
        ]);
        $invitation = Invitation::create([
            'event_id' => $event->id,
            'template_id' => $template->id,
            'slug' => 'alihan-aruzhan-12345',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $order = InvitationOrder::factory()->create([
            'template_id' => $template->id,
            'invitation_id' => $invitation->id,
            'status' => 'paid',
            'details' => ['names' => 'Алихан және Аружан'],
        ]);
        BonusTransaction::create([
            'restaurant_id' => $restaurant->id,
            'invitation_id' => $invitation->id,
            'invitation_order_id' => $order->id,
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
            ->assertSee('Алихан және Аружан')
            ->assertSee('Алтын салтанат')
            ->assertSee('/i/alihan-aruzhan-12345', false)
            ->assertDontSee('Әзірге бонус есептелмеді.');
    }

    public function test_partner_can_manage_packages_from_settings_without_services_ui(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();

        $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/settings/packages', [
            'name' => 'Премиум',
            'description' => 'Расширенное меню',
            'price_per_guest' => 22500,
        ])->assertRedirect('http://partner.zharzhar.kz/restaurant#settings');

        $service = RestaurantService::where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->assertDatabaseHas('restaurant_tariffs', [
            'restaurant_service_id' => $service->id,
            'name' => 'Премиум',
            'price_per_guest' => 22500,
            'is_active' => true,
        ]);

        $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant#settings')
            ->assertOk()
            ->assertSee('Премиум')
            ->assertSee('href="#settings"', false)
            ->assertDontSee('href="#services"', false)
            ->assertDontSee('name="default_price_per_guest"', false);
    }

    public function test_partner_can_download_utf8_csv_report(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();
        $tariff = $this->tariffFixture($restaurant, 18500);
        Booking::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'restaurant_tariff_id' => $tariff->id,
            'visitor_name' => 'Айдана',
            'event_type' => 'wedding',
            'phone' => '+7 700 111 22 33',
            'booking_date' => '2026-11-20',
            'guest_count' => 100,
            'price_per_guest' => 18500,
            'prepayment' => 100000,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant/reports/export?report_period=all');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename="zharzhar-bookings.csv"');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->getContent());
        $this->assertStringContainsString('Айдана', $response->getContent());
        $this->assertStringContainsString('Пакет', $response->getContent());
        $this->assertStringNotContainsString('Услуга', $response->getContent());
    }

    public function test_partner_searches_all_bookings_by_partial_name_or_normalized_phone(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();
        $target = Booking::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'visitor_name' => 'Айгерім Сейтова',
            'event_type' => 'wedding',
            'phone' => '+7 (707) 456-78-90',
            'booking_date' => '2027-02-18',
            'guest_count' => 80,
            'price_per_guest' => 15000,
            'prepayment' => 0,
            'status' => 'confirmed',
        ]);
        Booking::create([
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'visitor_name' => 'Другой гость',
            'event_type' => 'birthday',
            'phone' => '+7 700 000 00 01',
            'booking_date' => '2027-02-19',
            'guest_count' => 20,
            'price_per_guest' => 10000,
            'prepayment' => 0,
            'status' => 'confirmed',
        ]);

        foreach (['герім', '456789'] as $search) {
            $this->actingAs($partner)
                ->get('http://partner.zharzhar.kz/restaurant?q='.urlencode($search))
                ->assertOk()
                ->assertSee('Айгерім Сейтова')
                ->assertViewHas('bookings', fn ($bookings) => $bookings->count() === 1 && $bookings->first()->is($target));
        }
    }

    public function test_booking_price_is_taken_from_own_active_tariff(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();
        $tariff = $this->tariffFixture($restaurant, 18500);

        $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/bookings', [
            'visitor_name' => 'Айдана',
            'event_type' => 'wedding',
            'phone' => '+7 700 111 22 33',
            'booking_date' => '2026-11-20',
            'restaurant_slot_id' => $slot->id,
            'restaurant_tariff_id' => $tariff->id,
            'guest_count' => 100,
            'price_per_guest' => 1,
            'prepayment' => 100000,
        ])->assertRedirect();

        $booking = Booking::where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->assertSame($tariff->id, $booking->restaurant_tariff_id);
        $this->assertSame('18500.00', $booking->price_per_guest);
        $this->assertSame('confirmed', $booking->status);

        $tariff->update(['price_per_guest' => 20000]);
        $this->assertSame('18500.00', $booking->fresh()->price_per_guest);
    }

    public function test_partner_cannot_book_with_another_restaurants_tariff(): void
    {
        [$partner, , $slot] = $this->partnerFixture();
        [, $foreignRestaurant] = $this->partnerFixture();
        $foreignTariff = $this->tariffFixture($foreignRestaurant, 15000);

        $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/bookings', [
            'visitor_name' => 'Тест', 'event_type' => 'other', 'booking_date' => '2026-11-21',
            'restaurant_slot_id' => $slot->id, 'restaurant_tariff_id' => $foreignTariff->id,
            'guest_count' => 10, 'prepayment' => 0,
        ])->assertNotFound();
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

    private function tariffFixture(Restaurant $restaurant, float $price): RestaurantTariff
    {
        $service = RestaurantService::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Банкет',
            'is_active' => true,
        ]);

        return RestaurantTariff::create([
            'restaurant_service_id' => $service->id,
            'name' => 'Стандарт',
            'price_per_guest' => $price,
            'is_active' => true,
        ]);
    }
}

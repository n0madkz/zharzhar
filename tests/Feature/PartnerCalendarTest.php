<?php

namespace Tests\Feature;

use App\Models\BonusTransaction;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Invitation;
use App\Models\InvitationOrder;
use App\Models\PayoutRequest;
use App\Models\Restaurant;
use App\Models\RestaurantService;
use App\Models\RestaurantSlot;
use App\Models\RestaurantTariff;
use App\Models\StaffRequest;
use App\Models\Template;
use App\Models\User;
use App\Support\RestaurantInvitationCard;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    public function test_partner_can_open_restaurant_invitation_qr_card(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();

        $response = $this->actingAs($partner)
            ->get('http://partner.zharzhar.kz/restaurant/invitation-card');

        $response->assertOk()
            ->assertSee($restaurant->name)
            ->assertDontSee('+7 706 716 01 99')
            ->assertSee('Распечатать A5')
            ->assertSee('data:image/svg+xml;base64,', false);

        $whatsappUrl = app(RestaurantInvitationCard::class)->whatsappUrl($restaurant);
        $this->assertStringStartsWith('https://wa.me/77067160199?text=', $whatsappUrl);
        $this->assertStringContainsString(rawurlencode($restaurant->name), $whatsappUrl);
        $this->assertStringContainsString(rawurlencode('#'.$restaurant->id), $whatsappUrl);
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

    public function test_partner_can_request_kaspi_payout_once_per_month_from_ten_thousand(): void
    {
        Carbon::setTestNow('2026-09-15 12:00:00');
        try {
            [$partner, $restaurant] = $this->partnerFixture();
            BonusTransaction::create([
                'restaurant_id' => $restaurant->id,
                'amount' => 25000,
                'type' => 'accrual',
                'status' => 'available',
            ]);

            $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/bonuses/payout', [
                'amount' => 12000,
                'kaspi_phone' => '8 (707) 123-45-67',
            ])->assertRedirect('http://partner.zharzhar.kz/restaurant#bonuses');

            $this->assertDatabaseHas('payout_requests', [
                'restaurant_id' => $restaurant->id,
                'amount' => 12000,
                'kaspi_phone' => '+77071234567',
                'status' => 'pending',
            ]);
            $this->assertSame(13000.0, $restaurant->availableBonusBalance());

            $this->post('http://partner.zharzhar.kz/restaurant/bonuses/payout', [
                'amount' => 10000,
                'kaspi_phone' => '+7 707 123 45 67',
            ])->assertSessionHasErrors('amount');

            $this->get('http://partner.zharzhar.kz/restaurant#bonuses')
                ->assertOk()
                ->assertSee('13 000,00 ₸')
                ->assertSee('Осы айда шығару өтінімі берілген.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_rejected_payout_restores_balance_and_minimum_is_enforced(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();
        BonusTransaction::create(['restaurant_id' => $restaurant->id, 'amount' => 15000, 'type' => 'accrual', 'status' => 'available']);
        $payout = PayoutRequest::create(['restaurant_id' => $restaurant->id, 'amount' => 12000, 'kaspi_phone' => '+77071234567', 'status' => 'rejected']);
        $this->assertSame(15000.0, $restaurant->availableBonusBalance());

        $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/bonuses/payout', [
            'amount' => 9999,
            'kaspi_phone' => '+7 707 123 45 67',
        ])->assertSessionHasErrors('amount');
    }

    public function test_partner_can_download_pdf_report(): void
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
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="zharzhar-restaurant-report.pdf"');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $html = view('restaurant.reports-pdf', ['restaurant' => $restaurant, 'bookings' => Booking::with(['slot', 'tariff.service'])->get(), 'from' => null, 'to' => null])->render();
        $this->assertStringContainsString('Айдана', $html);
        $this->assertStringContainsString('Пакет', $html);
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

    public function test_partner_booking_list_is_paginated_by_ten(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();

        foreach (range(1, 12) as $number) {
            Booking::create([
                'restaurant_id' => $restaurant->id,
                'restaurant_slot_id' => $slot->id,
                'visitor_name' => 'Қонақ '.$number,
                'event_type' => 'wedding',
                'phone' => '+7 700 000 '.str_pad((string) $number, 4, '0', STR_PAD_LEFT),
                'booking_date' => '2027-03-10',
                'guest_count' => 20,
                'price_per_guest' => 10000,
                'prepayment' => 0,
                'status' => 'confirmed',
            ]);
        }

        $this->actingAs($partner)
            ->get('http://partner.zharzhar.kz/restaurant?month=2027-03&date=2027-03-10')
            ->assertOk()
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 12
                && $bookings->count() === 10
                && $bookings->lastPage() === 2);
    }

    public function test_partner_searches_upcoming_events_and_finds_past_events_in_archive(): void
    {
        [$partner, $restaurant, $slot] = $this->partnerFixture();
        $bookingData = [
            'restaurant_id' => $restaurant->id,
            'restaurant_slot_id' => $slot->id,
            'event_type' => 'wedding',
            'phone' => '+7 700 000 00 01',
            'guest_count' => 20,
            'price_per_guest' => 10000,
            'prepayment' => 0,
            'status' => 'confirmed',
        ];
        $future = Booking::create($bookingData + ['visitor_name' => 'Future Guest', 'booking_date' => Carbon::today()->addMonth()->toDateString()]);
        $past = Booking::create($bookingData + ['visitor_name' => 'Past Guest', 'booking_date' => Carbon::today()->subDay()->toDateString()]);

        $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant?q=Guest')
            ->assertOk()
            ->assertViewHas('bookingListMode', 'upcoming')
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 1 && $bookings->first()->is($future));

        $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant?view=archive&q=Guest')
            ->assertOk()
            ->assertViewHas('bookingListMode', 'archive')
            ->assertViewHas('bookings', fn ($bookings) => $bookings->total() === 1 && $bookings->first()->is($past));
    }

    public function test_waiter_can_apply_to_public_shift_and_partner_can_fill_it(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();
        $this->actingAs($partner)->post('http://partner.zharzhar.kz/restaurant/staff/requests', [
            'event_date' => Carbon::today()->addWeek()->toDateString(),
            'starts_at' => '17:00',
            'ends_at' => '23:00',
            'workers_needed' => 1,
            'pay_per_worker' => 18000,
            'description' => 'Wedding shift',
        ])->assertRedirect('http://partner.zharzhar.kz/restaurant#staff');

        $job = StaffRequest::where('restaurant_id', $restaurant->id)->firstOrFail();
        $this->get('http://zharzhar.kz/staff/jobs')->assertOk()->assertSee('Wedding shift');
        $waiter = User::factory()->create(['role' => 'staff', 'phone' => '77001234567']);
        $this->actingAs($waiter)->post('http://zharzhar.kz/staff/jobs/'.$job->id.'/apply', [
            'message' => 'Available',
        ])->assertRedirect();
        $this->assertSame(1, $job->applications()->count());
        $this->assertSame($waiter->id, $job->applications()->firstOrFail()->user_id);
        $this->get('http://zharzhar.kz/staff')->assertOk()->assertSee('На рассмотрении');
        $this->post('http://zharzhar.kz/staff/jobs/'.$job->id.'/apply')->assertSessionHasErrors('phone');
        $this->assertSame(1, $job->applications()->count());

        $application = $job->applications()->firstOrFail();
        $this->actingAs($partner)->put('http://partner.zharzhar.kz/restaurant/staff/requests/'.$job->id.'/applications/'.$application->id, [
            'status' => 'accepted',
        ])->assertRedirect('http://partner.zharzhar.kz/restaurant#staff');
        $this->assertSame('accepted', $application->fresh()->status);
        $this->assertSame('closed', $job->fresh()->status);
        $this->get('http://zharzhar.kz/staff/jobs')->assertDontSee('Wedding shift');
        $this->actingAs($waiter)->get('http://zharzhar.kz/staff')->assertOk()->assertSee('Принят');
    }

    public function test_staff_registration_fixes_role_and_redirects_to_own_dashboard(): void
    {
        $this->get('http://zharzhar.kz/staff/register')->assertOk();
        $this->post('http://zharzhar.kz/staff/register', [
            'name' => 'Waiter One', 'email' => 'waiter@example.test',
            'phone' => '8 (700) 123-45-67', 'password' => 'test-password',
            'password_confirmation' => 'test-password', 'role' => 'admin',
        ])->assertRedirect('http://zharzhar.kz/staff');
        $staff = User::where('email', 'waiter@example.test')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertSame('77001234567', $staff->phone);
        $this->assertTrue(Hash::check('test-password', $staff->password));
        $this->assertAuthenticatedAs($staff);
        $this->get('http://zharzhar.kz/dashboard')->assertRedirect('/staff');
        $this->get('http://partner.zharzhar.kz/restaurant')->assertForbidden();
    }

    public function test_staff_login_and_private_applications(): void
    {
        [$partner, $restaurant] = $this->partnerFixture();
        $staff = User::factory()->create(['role' => 'staff', 'phone' => '77001234567', 'password' => 'test-password']);
        $other = User::factory()->create(['role' => 'staff', 'phone' => '77007654321']);
        $job = $restaurant->staffRequests()->create([
            'event_date' => Carbon::today()->addWeek()->toDateString(), 'starts_at' => '17:00',
            'ends_at' => '23:00', 'workers_needed' => 2, 'pay_per_worker' => 18000,
        ]);
        $job->applications()->create(['user_id' => $other->id, 'name' => $other->name, 'phone' => $other->phone]);
        $this->post('http://zharzhar.kz/login', ['login' => $staff->email, 'password' => 'test-password'])->assertRedirect('/staff');
        $this->get('http://zharzhar.kz/staff')->assertOk()->assertViewHas('myApplications', fn ($applications) => $applications->isEmpty());
        $this->actingAs($partner)->post('http://zharzhar.kz/staff/jobs/'.$job->id.'/apply')->assertForbidden();
        $this->assertSame(1, $job->applications()->count());
    }

    public function test_partner_dashboard_remains_available_before_staff_migrations(): void
    {
        [$partner] = $this->partnerFixture();
        Schema::drop('staff_applications');
        Schema::drop('staff_requests');
        $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant?view=upcoming&q=')
            ->assertOk()->assertViewHas('staffEnabled', false);
        $this->get('http://zharzhar.kz/staff/jobs')->assertOk()->assertViewHas('staffEnabled', false);
        $this->post('http://partner.zharzhar.kz/restaurant/staff/requests')->assertStatus(503);
    }

    public function test_staff_feature_waits_for_application_user_migration(): void
    {
        [$partner] = $this->partnerFixture();
        Schema::table('staff_applications', function (Blueprint $table): void {
            $table->dropUnique(['staff_request_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
        $this->actingAs($partner)->get('http://partner.zharzhar.kz/restaurant')->assertOk()->assertViewHas('staffEnabled', false);
        $this->get('http://zharzhar.kz/staff/jobs')->assertOk()->assertViewHas('staffEnabled', false);
    }

    public function test_partner_cannot_manage_another_restaurants_staff_request(): void
    {
        [$owner, $restaurant] = $this->partnerFixture();
        [$other] = $this->partnerFixture();
        $job = $restaurant->staffRequests()->create([
            'event_date' => Carbon::today()->addWeek()->toDateString(),
            'starts_at' => '17:00', 'ends_at' => '23:00',
            'workers_needed' => 2, 'pay_per_worker' => 18000,
        ]);

        $this->actingAs($other)->put('http://partner.zharzhar.kz/restaurant/staff/requests/'.$job->id.'/close')->assertNotFound();
        $this->assertSame('open', $job->fresh()->status);
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

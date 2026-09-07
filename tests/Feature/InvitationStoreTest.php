<?php

namespace Tests\Feature;

use App\Models\InvitationOrder;
use App\Models\Music;
use App\Models\PromoCode;
use App\Models\Template;
use App\Models\User;
use Database\Seeders\InvitationCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class InvitationStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_with_simple_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@zharzhar.local',
            'role' => 'admin',
            'password' => Hash::make('Gamepro7'),
        ]);

        $response = $this->post('/login', [
            'login' => 'Admin1601',
            'password' => 'Gamepro7',
        ]);

        $response->assertRedirect('/admin/store');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_simple_login_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'admin@zharzhar.local',
            'role' => 'admin',
            'password' => Hash::make('Gamepro7'),
        ]);

        $response = $this->post('/login', [
            'login' => 'WrongLogin',
            'password' => 'Gamepro7',
        ]);

        $response->assertSessionHasErrors(['login' => 'Неверный логин или пароль.']);
        $this->assertGuest();
    }

    private function checkoutData(Template $template): array
    {
        return ['request_key' => (string) Str::uuid(), 'template_id' => $template->id, 'customer_name' => 'Айгүл', 'customer_phone' => '+7 700 123 45 67', 'event_type' => 'wedding', 'names' => 'Алихан и Аружан', 'hosts' => 'Ерлан – Айгүл', 'event_date' => now()->addMonth()->format('Y-m-d'), 'event_time' => '18:00', 'venue_name' => 'Салтанат', 'venue_address' => 'Алматы, Абая 1', 'language' => 'kk'];
    }

    private function placeOrder(array $data): TestResponse
    {
        return $this->withSession(['checkout_keys' => [$data['request_key'] => true]])->post('/checkout', $data);
    }

    public function test_catalog_filters_active_designs_and_shows_individual_prices(): void
    {
        $wedding = Template::factory()->create(['name' => 'Свадебный дизайн', 'price' => 9990]);
        Template::factory()->create(['name' => 'Скрытый дизайн', 'is_active' => false]);
        Template::factory()->create(['name' => 'Юбилейный дизайн', 'event_type' => 'anniversary']);
        $this->get('/?event=wedding')->assertOk()->assertSee('Свадебный дизайн')->assertSee('9 990')->assertDontSee('Скрытый дизайн')->assertDontSee('Юбилейный дизайн');
        $this->get('/designs/'.$wedding->id.'/preview')->assertOk();
        $this->get('/checkout/'.$wedding->id)->assertOk()->assertSee('Той иелері');
    }

    public function test_store_is_kazakh_by_default_and_language_switch_persists_russian(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Үлкен күн.')
            ->assertSee('ҚАЗ')
            ->assertSee('РУС')
            ->assertDontSee('Большой день.');

        $this->from('/')->post('/language/ru')->assertRedirect('/');
        $this->get('/')
            ->assertOk()
            ->assertSee('Большой день.')
            ->assertDontSee('Үлкен күн.');
        $this->post('/language/en')->assertNotFound();
    }

    public function test_seeded_mobile_designs_have_dedicated_preview_controls(): void
    {
        Template::factory()->create(['slug' => 'gold-wedding', 'is_active' => true]);
        $this->seed(InvitationCatalogSeeder::class);

        $this->assertDatabaseHas('templates', ['slug' => 'ak-inju', 'event_type' => 'wedding']);
        $this->assertDatabaseHas('templates', ['slug' => 'mereyli-shenber', 'event_type' => 'anniversary']);
        $this->assertDatabaseHas('templates', ['slug' => 'aru-qyz-uzatu', 'event_type' => 'qyz_uzatu']);
        $this->assertDatabaseHas('templates', ['slug' => 'gold-wedding', 'is_active' => false]);
        $this->assertDatabaseHas('templates', ['slug' => 'altyn-nomad', 'preview_image' => '/invitation-assets/nomad-horse.webp']);
        $this->assertSame(5, Template::where('event_type', 'wedding')->where('is_active', true)->pluck('preview_image')->unique()->count());

        $qyzUzatu = Template::where('slug', 'aru-qyz-uzatu')->firstOrFail();
        $royal = Template::where('slug', 'royal-kesh')->firstOrFail();

        $this->get('/?event=qyz_uzatu')
            ->assertOk()
            ->assertSee('Ару қыз ұзату')
            ->assertDontSee('Ақ інжу');

        $this->get('/designs/'.$qyzUzatu->id.'/preview')
            ->assertOk()
            ->assertSee('Шаблондарға қайту')
            ->assertSee('data-invite-music', false)
            ->assertSee('invite-theme-qyz', false);

        $this->get('/designs/'.$royal->id.'/preview')
            ->assertOk()
            ->assertSee('Әли &amp; Аяулым', false)
            ->assertSee('royal-ethno.webp')
            ->assertDontSee('Дастан')
            ->assertDontSee('Ләззат');
    }

    public function test_order_uses_server_price_and_discount_without_publishing_and_is_idempotent(): void
    {
        $template = Template::factory()->create();
        $promo = PromoCode::factory()->create(['code' => 'ZHAR10', 'max_uses' => 1]);
        $data = [...$this->checkoutData($template), 'promo_code' => 'zhar10', 'total' => 1, 'status' => 'paid'];
        $this->placeOrder($data)->assertRedirect();
        $order = InvitationOrder::firstOrFail();
        $this->assertSame(7191, $order->total);
        $this->assertSame(799, $order->discount);
        $this->assertSame('pending', $order->status);
        $this->assertDatabaseCount('invitations', 0);
        $this->assertSame(1, $promo->fresh()->uses);
        $this->placeOrder($data)->assertRedirect('/orders/'.$order->token);
        $this->assertDatabaseCount('invitation_orders', 1);
        $this->assertSame(1, $promo->fresh()->uses);
        $this->get('/orders/'.$order->token)->assertOk()->assertSee('+7 778 736 78 50')->assertDontSee($order->responses_token)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/responses/'.$order->responses_token)->assertNotFound();
        $this->get('/orders/not-a-real-token')->assertNotFound();
    }

    #[TestWith(['percent', 100, 7990, 0])]
    #[TestWith(['fixed', 1000, 1000, 6990])]
    #[TestWith(['fixed', 9000, 7990, 0])]
    public function test_discount_calculation_is_capped_at_price(string $type, int $value, int $discount, int $total): void
    {
        $template = Template::factory()->create();
        $promo = PromoCode::factory()->create(compact('type', 'value'));
        $this->postJson('/checkout/quote', ['template_id' => $template->id, 'promo_code' => $promo->code])->assertOk()->assertJson(compact('discount', 'total'));
        $this->assertSame(0, $promo->fresh()->uses);
    }

    #[TestWith(['inactive'])]
    #[TestWith(['expired'])]
    #[TestWith(['exhausted'])]
    #[TestWith(['missing'])]
    public function test_invalid_promos_cannot_be_redeemed(string $case): void
    {
        $template = Template::factory()->create();
        $promo = PromoCode::factory()->create([
            'is_active' => $case !== 'inactive', 'expires_at' => $case === 'expired' ? now()->subMinute() : null,
            'max_uses' => 1, 'uses' => $case === 'exhausted' ? 1 : 0,
        ]);
        $this->placeOrder([...$this->checkoutData($template), 'promo_code' => $case === 'missing' ? 'UNKNOWN' : $promo->code])->assertSessionHasErrors('promo_code');
        $this->assertDatabaseCount('invitation_orders', 0);
    }

    public function test_payment_button_sends_order_to_review_and_opens_whatsapp(): void
    {
        $order = InvitationOrder::factory()->create();
        $this->get('/orders/'.$order->token)
            ->assertOk()
            ->assertSee('Төледім — WhatsApp-қа жазу')
            ->assertDontSee('payment_reference')
            ->assertDontSee('<textarea', false);

        $response = $this->post('/orders/'.$order->token.'/payment');

        $response->assertRedirect();
        $this->assertStringStartsWith('https://wa.me/77787367850?text=', $response->headers->get('Location'));
        $this->assertStringContainsString(rawurlencode('№'.$order->id), $response->headers->get('Location'));
        $this->assertSame('review', $order->fresh()->status);
        $this->assertNull($order->fresh()->payment_reference);
        $this->assertDatabaseCount('invitations', 0);
    }

    public function test_admin_confirmation_publishes_once_and_guest_answers_remain_private(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = InvitationOrder::factory()->create(['status' => 'review']);
        $this->actingAs($admin)->post('/admin/store/orders/'.$order->id.'/confirm')->assertRedirect();
        $this->post('/admin/store/orders/'.$order->id.'/confirm')->assertRedirect();
        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame($admin->id, $order->confirmed_by);
        $this->assertDatabaseCount('invitations', 1);
        $this->assertDatabaseCount('events', 1);
        $this->assertMatchesRegularExpression('/^alihan-aruzhan-[0-9]{5}$/', $order->invitation->slug);
        $this->get('/orders/'.$order->token)->assertSee($order->responses_token);
        $slug = $order->invitation->slug;
        $this->get('/i/'.$slug)->assertOk()->assertSee('Ерлан')->assertDontSee($order->responses_token);
        $this->post('/i/'.$slug.'/rsvp', ['guest_name' => '<script>alert(1)</script>', 'attendance_status' => 'yes', 'guest_count' => 3, 'message' => 'Рақмет!'])->assertRedirect();
        $this->get('/responses/'.$order->responses_token)->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('Рақмет!');
        $this->post('/i/'.$slug.'/rsvp', ['guest_name' => 'Айдос', 'attendance_status' => 'no', 'guest_count' => 2])->assertRedirect();
        $this->assertDatabaseCount('rsvps', 1);
        $this->assertDatabaseHas('rsvps', ['attendance_status' => 'no', 'guest_count' => 0]);
    }

    public function test_rejection_releases_promo_once_and_prevents_confirmation(): void
    {
        $promo = PromoCode::factory()->create(['uses' => 1]);
        $order = InvitationOrder::factory()->create(['promo_code_id' => $promo->id]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/store/orders/'.$order->id.'/reject', ['admin_note' => 'Перевод не найден'])->assertRedirect();
        $this->post('/admin/store/orders/'.$order->id.'/reject', ['admin_note' => 'Перевод не найден'])->assertRedirect();
        $this->assertSame(0, $promo->fresh()->uses);
        $this->post('/admin/store/orders/'.$order->id.'/confirm')->assertConflict();
        $this->assertDatabaseCount('invitations', 0);
    }

    #[TestWith(['client'])]
    #[TestWith(['partner'])]
    public function test_non_admin_roles_cannot_access_or_mutate_store(string $role): void
    {
        $order = InvitationOrder::factory()->create();
        $this->actingAs(User::factory()->create(compact('role')));
        $this->get('/admin/store')->assertForbidden();
        foreach (['orders/'.$order->id.'/confirm', 'orders/'.$order->id.'/reject', 'templates', 'music', 'promos', 'restaurants'] as $path) {
            $this->post('/admin/store/'.$path)->assertForbidden();
        }
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_admin_subdomain_is_enforced_and_authentication_required(): void
    {
        $this->get('http://admin.zharzhar.kz/admin/store')->assertRedirect('http://admin.zharzhar.kz/login');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('http://zharzhar.kz/admin/store')->assertNotFound();
        $this->get('http://admin.zharzhar.kz/admin/store')->assertOk();
        $this->get('http://admin.zharzhar.kz/')->assertRedirect('/admin/store');
    }

    public function test_admin_can_manage_catalog_and_generate_codes_with_validation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['name' => 'Розовый сад', 'slug' => 'rose-test', 'price' => 9990, 'theme' => 'rose', 'event_type' => 'wedding', 'is_active' => 1];
        $this->post('/admin/store/templates', $data)->assertRedirect();
        $template = Template::firstOrFail();
        $this->post('/admin/store/templates/'.$template->id, [...$data, 'price' => 500])->assertSessionHasErrors('price');
        $this->post('/admin/store/templates/'.$template->id, [...$data, 'price' => 12990, 'is_active' => 0])->assertRedirect();
        $this->assertDatabaseHas('templates', ['id' => $template->id, 'price' => 12990, 'is_active' => false]);
        $this->post('/admin/store/music', [
            'name' => 'Той',
            'categories' => ['wedding', 'qyz_uzatu', 'birthday'],
            'audio_file' => UploadedFile::fake()->create('toi.mp3', 1024, 'audio/mpeg'),
            'is_active' => 1,
        ])->assertRedirect();
        $music = Music::firstOrFail();
        $this->assertSame(['wedding', 'qyz_uzatu', 'birthday'], $music->categories);
        $this->assertStringStartsWith('/storage/music/', $music->audio_url);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $music->audio_url));
        $this->post('/admin/store/music', [
            'name' => 'Без категории',
            'categories' => [],
            'audio_file' => UploadedFile::fake()->create('track.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors(['categories', 'audio_file']);
        $this->post('/admin/store/promos', ['type' => 'percent', 'value' => 101, 'is_active' => 1])->assertSessionHasErrors('value');
        $this->post('/admin/store/promos', ['type' => 'percent', 'value' => 15, 'max_uses' => 10, 'is_active' => 1])->assertRedirect();
        $this->assertStringStartsWith('ZHAR-', PromoCode::firstOrFail()->code);
        $this->post('/admin/store/restaurants', ['name' => 'Салтанат', 'city' => 'Алматы', 'address' => 'Абая 1', 'is_active' => 1])->assertRedirect();
        $this->get('/admin/store')->assertOk()->assertSee('Розовый сад')->assertSee('Салтанат')->assertSee('Қыз ұзату');
    }

    public function test_checkout_only_shows_music_for_its_event_category(): void
    {
        $template = Template::factory()->create(['event_type' => 'wedding']);
        Music::create(['name' => 'Свадебная песня', 'category' => 'Свадьба', 'categories' => ['wedding', 'anniversary'], 'audio_url' => '/storage/music/wedding.mp3', 'is_active' => true]);
        Music::create(['name' => 'Песня на день рождения', 'category' => 'День рождения', 'categories' => ['birthday'], 'audio_url' => '/storage/music/birthday.mp3', 'is_active' => true]);

        $this->get('/checkout/'.$template->id)
            ->assertOk()
            ->assertSee('Свадебная песня')
            ->assertDontSee('Песня на день рождения');
    }

    public function test_inactive_resources_and_invalid_event_data_cannot_be_ordered(): void
    {
        $template = Template::factory()->create();
        $data = $this->checkoutData($template);
        $this->placeOrder([...$data, 'event_date' => '2020-01-01', 'hosts' => '', 'music_id' => 99999, 'restaurant_id' => 99999])->assertSessionHasErrors(['event_date', 'hosts', 'music_id', 'restaurant_id']);
        $this->placeOrder([...$data, 'event_type' => 'birthday'])->assertSessionHasErrors('event_type');
        $template->update(['is_active' => false]);
        $this->placeOrder($data)->assertSessionHasErrors('template_id');
        $this->get('/checkout/'.$template->id)->assertNotFound();
        $this->assertDatabaseCount('invitation_orders', 0);
    }
}

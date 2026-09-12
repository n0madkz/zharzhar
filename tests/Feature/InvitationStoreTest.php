<?php

namespace Tests\Feature;

use App\Models\InvitationOrder;
use App\Models\Music;
use App\Models\PromoCode;
use App\Models\Restaurant;
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

    private function adminTemplateData(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Ақ арман', 'slug' => 'ak-arman', 'price' => 9990, 'theme' => 'pearl',
            'event_type' => 'wedding', 'is_active' => 1,
            'content_title' => 'Ақ арман', 'content_event_label' => 'ҮЙЛЕНУ ТОЙЫ',
            'content_intro_title' => 'АҚ ТІЛЕКПЕН БАСТАЛҒАН КҮН',
            'content_invitation_text' => 'Қуанышымыздың қадірлі қонағы болуға шақырамыз.',
            'content_event_date' => now()->addMonth()->format('Y-m-d'), 'content_event_time' => '18:00',
            'content_date_title' => 'Той салтанаты', 'content_program_title' => 'Той бағдарламасы',
            'content_welcome_text' => 'Қонақтардың жиналуы', 'content_ceremony_text' => 'Салтанатты рәсім',
            'content_celebration_text' => 'Мерекелік кеш', 'content_venue_title' => 'Мекенжайымыз',
            'content_venue_name' => 'Ақ Отау', 'content_venue_address' => 'Алматы қаласы, Абай даңғылы, 50',
            'content_countdown_title' => 'Салтанатқа дейін', 'content_hosts_title' => 'Той иелері',
            'content_hosts_name' => 'Қуаныш иелері', 'content_rsvp_title' => 'Сізді күтеміз!',
            'content_rsvp_hint' => 'Қатысуыңызды растауыңызды сұраймыз.',
            'content_closing_text' => 'Қуанышымызға ортақ болыңыз!',
        ], $overrides);
    }

    private function adminOrderData(InvitationOrder $order, array $overrides = []): array
    {
        $details = $order->details;

        return array_replace([
            'status' => $order->status,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'template_id' => $order->template_id,
            'event_type' => $details['event_type'],
            'names' => $details['names'],
            'hosts' => $details['hosts'],
            'event_date' => $details['event_date'],
            'event_time' => $details['event_time'],
            'restaurant_id' => $details['restaurant_id'] ?? null,
            'venue_name' => $details['venue_name'],
            'venue_address' => $details['venue_address'],
            'language' => $details['language'],
            'music_id' => $details['music_id'] ?? null,
            'invitation_text' => $details['invitation_text'] ?? '',
            'program_times' => ['17:00', '18:00', '19:00'],
            'copy' => [
                'event_label' => 'ҮЙЛЕНУ ТОЙЫ', 'intro' => 'ҚҰРМЕТТІ ҚОНАҚТАР!', 'date_title' => 'Той салтанаты',
                'program' => 'Той бағдарламасы', 'welcome' => 'Қонақтардың жиналуы', 'ceremony' => 'Салтанатты рәсім',
                'celebration' => 'Мерекелік кеш', 'venue' => 'Мекенжайымыз', 'map' => 'Картадан көру',
                'countdown' => 'Салтанатқа дейін', 'days' => 'күн', 'hours' => 'сағат', 'minutes' => 'минут',
                'seconds' => 'секунд', 'hosts' => 'Той иелері', 'rsvp' => 'Сізді күтеміз!',
                'hint' => 'Қатысуыңызды растаңыз.', 'name' => 'Аты-жөніңіз', 'answer' => 'Тойға қатысасыз ба?',
                'yes' => 'Иә, қатысамын', 'no' => 'Қатыса алмаймын', 'maybe' => 'Кейін айтамын',
                'count' => 'Қонақ саны', 'message' => 'Ақ тілегіңіз', 'send' => 'Жауап жіберу',
                'closing' => 'Қуанышымызға ортақ болыңыз!',
            ],
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'total' => $order->total,
            'promo_code' => $order->promo_code,
            'payment_reference' => $order->payment_reference,
            'admin_note' => $order->admin_note,
        ], $overrides);
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

    public function test_uploaded_music_is_served_without_a_public_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('music/invitation.mp3', 'fake-mp3-content');

        $this->get('/media/music/invitation.mp3')
            ->assertOk()
            ->assertHeader('cache-control', 'max-age=86400, public');

        $this->get('/media/music/missing.mp3')->assertNotFound();
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
            ->assertSee('Оплата по Kaspi Pay')
            ->assertDontSee('Переведите сумму')
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
        $this->assertDatabaseHas('templates', ['slug' => 'royal-kesh', 'name' => 'Алтын салтанат']);
        $this->assertDatabaseHas('templates', ['slug' => 'altyn-nomad', 'name' => 'Дала мұрасы']);
        $this->assertDatabaseHas('templates', ['slug' => 'gold-wedding', 'is_active' => false]);
        $this->assertDatabaseHas('templates', ['slug' => 'altyn-nomad', 'preview_image' => '/invitation-assets/nomad-horse.webp']);
        $this->assertSame(5, Template::where('event_type', 'wedding')->where('is_active', true)->pluck('preview_image')->unique()->count());

        foreach (Template::whereIn('slug', [
            'ak-inju', 'royal-kesh', 'nazik-botanika', 'ak-zhibek',
            'altyn-nomad', 'mereyli-shenber', 'aru-qyz-uzatu', 'dala-shattygy',
        ])->get() as $design) {
            $this->get('/designs/'.$design->id.'/preview')
                ->assertOk()
                ->assertSee('music-theme-'.$design->config_json['theme'], false)
                ->assertSee('aria-pressed="false"', false)
                ->assertSee('music-play-icon', false)
                ->assertSee('music-pause-icon', false);
        }

        $musicStyles = file_get_contents(public_path('invitation.css'));
        $this->assertStringContainsString('.music-orb{', $musicStyles);
        $this->assertStringContainsString('position:fixed', $musicStyles);
        $this->assertStringContainsString('right:max(16px,env(safe-area-inset-right))', $musicStyles);
        $catalogStyles = file_get_contents(public_path('motion.css'));
        $this->assertStringContainsString('.catalog-grid {', $catalogStyles);
        $this->assertStringContainsString('grid-template-columns: repeat(4, minmax(0, 1fr))', $catalogStyles);
        $this->assertStringNotContainsString('scroll-snap-type: inline mandatory', $catalogStyles);

        $qyzUzatu = Template::where('slug', 'aru-qyz-uzatu')->firstOrFail();
        $royal = Template::where('slug', 'royal-kesh')->firstOrFail();

        $this->get('/?event=qyz_uzatu')
            ->assertOk()
            ->assertSee('Ару қыз ұзату');

        $this->get('/designs/'.$qyzUzatu->id.'/preview')
            ->assertOk()
            ->assertSee('Шаблондарға қайту')
            ->assertSee('data-invite-music', false)
            ->assertSee('invite-theme-qyz', false)
            ->assertSee('<span>Ару</span><span>қыз ұзату</span>', false)
            ->assertSee('two-gis-logo', false)
            ->assertSee('2GIS-те ашу')
            ->assertDontSee('round-map', false);

        $this->get('/designs/'.$royal->id.'/preview')
            ->assertOk()
            ->assertSee('Алтын салтанат')
            ->assertSee('ДОМБЫРА ҮНІМЕН ӨРІЛГЕН ҚУАНЫШ')
            ->assertSee('royal-ethno.webp')
            ->assertDontSee('Дастан')
            ->assertDontSee('Ләззат');

        $this->withSession(['store_locale' => 'ru'])
            ->get('/designs/'.$royal->id.'/preview')
            ->assertOk()
            ->assertSee('Той бағдарламасы')
            ->assertDontSee('Программа вечера');
    }

    public function test_order_uses_server_price_and_discount_without_publishing_and_is_idempotent(): void
    {
        $template = Template::factory()->create();
        $promo = PromoCode::factory()->create(['code' => 'ZHAR10', 'max_uses' => 1]);
        $restaurant = Restaurant::create([
            'name' => 'Ақ Отау', 'city' => 'Алматы', 'address' => 'Абай 50',
            'two_gis_url' => 'https://2gis.kz/almaty/firm/123456', 'phone' => '+7 700 111 22 33', 'status' => 'active',
        ]);
        $data = [...$this->checkoutData($template), 'restaurant_id' => $restaurant->id, 'promo_code' => 'zhar10', 'total' => 1, 'status' => 'paid'];
        $this->placeOrder($data)->assertRedirect();
        $order = InvitationOrder::firstOrFail();
        $this->assertSame(7191, $order->total);
        $this->assertSame(799, $order->discount);
        $this->assertSame('pending', $order->status);
        $this->assertSame($restaurant->two_gis_url, $order->details['two_gis_url']);
        $this->assertDatabaseCount('invitations', 0);
        $this->assertSame(1, $promo->fresh()->uses);
        $this->placeOrder($data)->assertRedirect('/orders/'.$order->token);
        $this->assertDatabaseCount('invitation_orders', 1);
        $this->assertSame(1, $promo->fresh()->uses);
        $this->get('/orders/'.$order->token)->assertOk()->assertSee('+7 778 736 78 50')->assertDontSee($order->responses_token)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/responses/'.$order->responses_token)->assertNotFound();
        $this->get('/orders/not-a-real-token')->assertNotFound();
    }

    public function test_selected_language_is_used_by_the_published_invitation(): void
    {
        $template = Template::factory()->create();
        $this->placeOrder([...$this->checkoutData($template), 'language' => 'ru'])->assertRedirect();
        $order = InvitationOrder::firstOrFail();
        $this->assertSame('ru', $order->details['language']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post('/admin/store/orders/'.$order->id.'/confirm')->assertRedirect();

        $this->get('/i/'.$order->fresh()->invitation->slug)
            ->assertOk()
            ->assertSee('ДОРОГИЕ РОДНЫЕ И ДРУЗЬЯ!')
            ->assertSee('Дата торжества')
            ->assertDontSee('ҚҰРМЕТТІ АҒАЙЫН-ТУЫС');
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
            ->assertSee('+7 778 736 78 50')
            ->assertSee('Айдын Б.')
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
        $this->assertDatabaseCount('rsvps', 2);
        $this->assertDatabaseHas('rsvps', ['guest_name' => '<script>alert(1)</script>', 'attendance_status' => 'yes', 'guest_count' => 3]);
        $this->assertDatabaseHas('rsvps', ['guest_name' => 'Айдос', 'attendance_status' => 'no', 'guest_count' => 0]);
    }

    public function test_paid_invitation_accrues_restaurant_bonus_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $restaurant = Restaurant::create([
            'name' => 'Bonus Hall',
            'city' => 'Алматы',
            'address' => 'Абая, 10',
            'bonus_percent' => 7.5,
            'status' => 'active',
        ]);
        $order = InvitationOrder::factory()->create(['status' => 'review', 'subtotal' => 10000, 'total' => 10000]);
        $order->update(['details' => [...$order->details, 'restaurant_id' => $restaurant->id]]);

        $this->actingAs($admin)->post('/admin/store/orders/'.$order->id.'/confirm')->assertRedirect();
        $this->post('/admin/store/orders/'.$order->id.'/confirm')->assertRedirect();

        $this->assertDatabaseCount('bonus_transactions', 1);
        $this->assertDatabaseHas('bonus_transactions', [
            'restaurant_id' => $restaurant->id,
            'invitation_order_id' => $order->id,
            'amount' => 750,
            'type' => 'accrual',
            'status' => 'available',
        ]);
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
        $this->get('/admin/store/orders/'.$order->id.'/edit')->assertForbidden();
        $this->put('/admin/store/orders/'.$order->id)->assertForbidden();
        $this->delete('/admin/store/orders/'.$order->id)->assertForbidden();
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

    public function test_admin_orders_are_paginated_and_searchable(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $template = Template::factory()->create();
        InvitationOrder::factory()->count(11)->create(['template_id' => $template->id]);
        $target = InvitationOrder::factory()->make([
            'template_id' => $template->id,
            'customer_name' => 'Мария Касымова',
            'customer_phone' => '+7 777 555 66 77',
            'status' => 'review',
        ]);
        $target->details = array_replace($target->details, [
            'names' => 'Арман и Аяла',
            'venue_name' => 'Алтын Сарай',
        ]);
        $target->save();

        $firstPage = $this->get(route('admin.store.index'));
        $firstPage->assertOk()->assertViewHas('orders', fn ($orders) => $orders->perPage() === 10 && $orders->count() === 10 && $orders->lastPage() === 2);
        $this->get(route('admin.store.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->count() === 2);

        foreach (['Мария', '77775556677', 'Алтын Сарай', (string) $target->id] as $search) {
            $this->get(route('admin.store.index', ['q' => $search]))
                ->assertOk()
                ->assertSee('Мария Касымова')
                ->assertViewHas('orders', fn ($orders) => $orders->total() === 1 && $orders->first()->is($target));
        }

        $this->get(route('admin.store.index', ['status' => 'pending', 'q' => 'Мария']))
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->isEmpty());
    }

    public function test_admin_can_manage_catalog_and_generate_codes_with_validation(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = $this->adminTemplateData([
            'preview_image_file' => UploadedFile::fake()->image('ak-arman.png', 800, 1000),
        ]);
        $this->post('/admin/store/templates', $data)->assertRedirect();
        $template = Template::firstOrFail();
        $this->post('/admin/store/templates/'.$template->id, [...$data, 'price' => 500])->assertSessionHasErrors('price');
        $this->post('/admin/store/templates/'.$template->id, [...$data, 'name' => 'Жаңарған ақ арман', 'content_invitation_text' => 'Жаңартылған қазақша шақыру мәтіні.', 'price' => 12990, 'is_active' => 0])->assertRedirect();
        $this->assertDatabaseHas('templates', ['id' => $template->id, 'price' => 12990, 'is_active' => false]);
        $template->refresh();
        $this->assertSame('Жаңарған ақ арман', $template->name);
        $this->assertSame('Жаңартылған қазақша шақыру мәтіні.', $template->config_json['content_kk']['invitation_text']);
        $this->assertStringStartsWith('/storage/designs/', $template->preview_image);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $template->preview_image));
        $this->post('/admin/store/music', [
            'name' => 'Той',
            'categories' => ['wedding', 'qyz_uzatu', 'birthday'],
            'audio_file' => UploadedFile::fake()->create('toi.mp3', 1024, 'audio/mpeg'),
            'is_active' => 1,
        ])->assertRedirect();
        $music = Music::firstOrFail();
        $this->assertSame(['wedding', 'qyz_uzatu', 'birthday'], $music->categories);
        $this->assertStringStartsWith('/media/music/', $music->audio_url);
        Storage::disk('public')->assertExists(str_replace('/media/', '', $music->audio_url));
        $this->post('/admin/store/music', [
            'name' => 'Без категории',
            'categories' => [],
            'audio_file' => UploadedFile::fake()->create('track.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors(['categories', 'audio_file']);
        $this->post('/admin/store/promos', ['type' => 'percent', 'value' => 101, 'is_active' => 1])->assertSessionHasErrors('value');
        $this->post('/admin/store/promos', ['type' => 'percent', 'value' => 15, 'max_uses' => 10, 'is_active' => 1])->assertRedirect();
        $this->assertStringStartsWith('ZHAR-', PromoCode::firstOrFail()->code);
        $this->post('/admin/store/restaurants', ['name' => 'Салтанат', 'city' => 'Алматы', 'address' => 'Абая 1', 'is_active' => 1])->assertRedirect();
        $this->get('/admin/store')->assertOk()->assertSee('Жаңарған ақ арман')->assertSee('Салтанат')->assertSee('Қыз ұзату')->assertSee('Главный заголовок превью');
    }

    public function test_admin_can_edit_every_order_field_sync_published_invitation_and_delete_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $promo = PromoCode::factory()->create(['uses' => 1]);
        $order = InvitationOrder::factory()->create(['status' => 'review', 'promo_code_id' => $promo->id, 'promo_code' => $promo->code]);
        $newTemplate = Template::factory()->create(['name' => 'Алтын өрнек', 'config_json' => ['theme' => 'royal']]);
        $restaurant = Restaurant::create(['name' => 'Ақ Сарай', 'city' => 'Алматы', 'address' => 'Достық 10', 'status' => 'active']);
        $music = Music::create(['name' => 'Ақ той', 'category' => 'Свадьба', 'categories' => ['wedding'], 'audio_url' => '/storage/music/ak-toi.mp3', 'is_active' => true]);

        $this->actingAs($admin)->get('/admin/store/orders/'.$order->id.'/edit')
            ->assertOk()
            ->assertSee('Все тексты внутри приглашения')
            ->assertSee('Удалить заказ навсегда');

        $payload = $this->adminOrderData($order, [
            'status' => 'paid',
            'customer_name' => 'Жаңарған клиент',
            'customer_phone' => '+7 777 111 22 33',
            'template_id' => $newTemplate->id,
            'names' => 'Арман & Аяла',
            'hosts' => 'Нұрлан – Гүлнар',
            'restaurant_id' => $restaurant->id,
            'venue_name' => 'Ақ Сарай',
            'venue_address' => 'Алматы, Достық 10',
            'music_id' => $music->id,
            'invitation_text' => 'Арнайы жаңартылған шақыру мәтіні.',
            'program_times' => ['16:30', '18:15', '20:00'],
            'subtotal' => 15990,
            'discount' => 2000,
            'total' => 13990,
            'admin_note' => 'Клиентпен келісілді.',
        ]);
        $payload['copy']['event_label'] = 'АРМАН МЕН АЯЛАНЫҢ ТОЙЫ';
        $payload['copy']['send'] = 'Жауабымды сақтау';
        $payload['copy']['closing'] = 'Ақ тілегіңізбен келіңіз!';

        $this->put('/admin/store/orders/'.$order->id, $payload)->assertRedirect('/admin/store/orders/'.$order->id.'/edit');
        $order->refresh();
        $this->assertSame('Жаңарған клиент', $order->customer_name);
        $this->assertSame(13990, $order->total);
        $this->assertSame('Арман & Аяла', $order->details['names']);
        $this->assertSame(['16:30', '18:15', '20:00'], $order->details['program_times']);
        $this->assertSame('Жауабымды сақтау', $order->details['copy']['send']);
        $this->assertSame($newTemplate->id, $order->invitation->template_id);
        $this->assertSame('Арман & Аяла', $order->invitation->event->title);
        $this->assertSame($restaurant->id, $order->invitation->event->restaurant_id);
        $invitationId = $order->invitation_id;
        $eventId = $order->invitation->event_id;
        $this->get('/i/'.$order->invitation->slug)
            ->assertOk()
            ->assertSee('АРМАН МЕН АЯЛАНЫҢ ТОЙЫ')
            ->assertSee('Арнайы жаңартылған шақыру мәтіні.')
            ->assertSee('Жауабымды сақтау')
            ->assertSee('Ақ тілегіңізбен келіңіз!')
            ->assertSee('16:30');
        $this->post('/i/'.$order->invitation->slug.'/rsvp', ['guest_name' => 'Қонақ', 'attendance_status' => 'yes', 'guest_count' => 2])->assertRedirect();

        $this->delete('/admin/store/orders/'.$order->id)->assertRedirect('/admin/store');
        $this->assertDatabaseMissing('invitation_orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('invitations', ['id' => $invitationId]);
        $this->assertDatabaseMissing('events', ['id' => $eventId]);
        $this->assertDatabaseCount('rsvps', 0);
        $this->assertSame(0, $promo->fresh()->uses);
    }

    public function test_partner_subdomain_opens_booking_service_and_restaurant_routes_are_isolated(): void
    {
        $this->get('http://partner.zharzhar.kz/')->assertRedirect('http://partner.zharzhar.kz/login');
        $this->get('http://zharzhar.kz/restaurant')->assertNotFound();
        $this->get('http://partner.zharzhar.kz/restaurant')->assertRedirect('http://partner.zharzhar.kz/login');
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

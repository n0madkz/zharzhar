<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\BonusTransaction;
use App\Models\Invitation;
use App\Models\InvitationOrder;
use App\Models\Music;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim(mb_substr($request->string('q')->toString(), 0, 100));
        $phoneSearch = preg_replace('/\D+/', '', $search);
        $orderIdSearch = ctype_digit($search) && strlen($search) <= 6 ? (int) $search : null;
        if (preg_match('/^(?:заказ\s*)?[№#]\s*(\d+)$/ui', $search, $matches)) {
            $orderIdSearch = (int) $matches[1];
        }
        $orders = InvitationOrder::with('invitation')
            ->when(in_array($status, ['pending', 'review', 'paid', 'rejected']), fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search, $phoneSearch, $orderIdSearch): void {
                $query->where(function ($query) use ($search, $phoneSearch, $orderIdSearch): void {
                    if ($orderIdSearch !== null) {
                        $query->whereKey($orderIdSearch);

                        return;
                    }
                    $query->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$search.'%')
                        ->orWhere('details->names', 'like', '%'.$search.'%')
                        ->orWhere('details->hosts', 'like', '%'.$search.'%')
                        ->orWhere('details->venue_name', 'like', '%'.$search.'%');
                    if ($phoneSearch !== '') {
                        $query->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE ?", ['%'.$phoneSearch.'%']);
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.store', [
            'orders' => $orders, 'status' => $status, 'search' => $search,
            'totals' => ['review' => InvitationOrder::where('status', 'review')->count(), 'paid' => InvitationOrder::where('status', 'paid')->count(), 'revenue' => InvitationOrder::where('status', 'paid')->sum('total')],
            'templates' => Template::orderBy('price')->get(), 'music' => Music::latest()->get(),
            'promos' => PromoCode::latest()->get(), 'restaurants' => Restaurant::orderBy('name')->get(),
        ]);
    }

    public function confirm(Request $request, InvitationOrder $order): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($order, $request, $data) {
            $order = InvitationOrder::lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'paid') {
                return;
            }
            abort_unless(in_array($order->status, ['pending', 'review']), 409, 'Этот заказ уже закрыт.');
            $details = $order->details;
            $event = Event::create([
                'user_id' => null, 'restaurant_id' => $details['restaurant_id'] ?? null, 'event_type' => $details['event_type'],
                'title' => $details['names'], 'event_date' => $details['event_date'], 'event_time' => $details['event_time'],
                'venue_name' => $details['venue_name'], 'venue_address' => $details['venue_address'], 'language' => $details['language'], 'status' => 'active',
            ]);
            $invitation = Invitation::create([
                'event_id' => $event->id, 'template_id' => $order->template_id, 'slug' => $this->invitationSlug($details['names']),
                'content_json' => $details, 'settings_json' => ['music_url' => $details['music_url'] ?? null, 'theme' => $details['theme']],
                'status' => 'published', 'published_at' => now(),
            ]);
            $order->update(['status' => 'paid', 'paid_at' => now(), 'confirmed_by' => $request->user()->id, 'invitation_id' => $invitation->id, 'admin_note' => $data['admin_note'] ?? null]);
            $this->syncRestaurantBonus($order->refresh());
        }, 3);

        return back()->with('success', 'Оплата подтверждена. Обе ссылки созданы и доступны клиенту на странице заказа.');
    }

    public function editOrder(InvitationOrder $order): View
    {
        $order->load(['invitation.event', 'template']);
        $details = $order->details ?? [];
        $musicId = $details['music_id'] ?? null;
        if (! $musicId && ! empty($details['music_url'])) {
            $musicId = Music::where('audio_url', $details['music_url'])->value('id');
        }
        $copy = array_replace(
            $this->invitationCopyDefaults($details['language'] ?? 'kk', $details['event_type'] ?? 'wedding'),
            $details['copy'] ?? [],
        );

        return view('admin.order-edit', [
            'order' => $order,
            'details' => $details,
            'copy' => $copy,
            'musicId' => $musicId,
            'templates' => Template::orderBy('name')->get(),
            'music' => Music::orderBy('name')->get(),
            'restaurants' => Restaurant::orderBy('name')->get(),
        ]);
    }

    public function updateOrder(Request $request, InvitationOrder $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'review', 'paid', 'rejected'])],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'regex:/^\\+?[0-9 ()-]{10,25}$/'],
            'template_id' => ['required', 'integer', Rule::exists('templates', 'id')],
            'event_type' => ['required', Rule::in(array_keys(config('store.event_types')))],
            'names' => ['required', 'string', 'max:160'],
            'hosts' => ['required', 'string', 'max:240'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'event_time' => ['required', 'date_format:H:i'],
            'restaurant_id' => ['nullable', 'integer', Rule::exists('restaurants', 'id')],
            'venue_name' => ['required', 'string', 'max:160'],
            'venue_address' => ['required', 'string', 'max:255'],
            'language' => ['required', Rule::in(['kk', 'ru'])],
            'music_id' => ['nullable', 'integer', Rule::exists('music', 'id')],
            'invitation_text' => ['nullable', 'string', 'max:2000'],
            'program_times' => ['required', 'array', 'size:3'],
            'program_times.*' => ['required', 'date_format:H:i'],
            'copy' => ['required', 'array'],
            'copy.*' => ['required', 'string', 'max:300'],
            'subtotal' => ['required', 'integer', 'min:0', 'max:10000000'],
            'discount' => ['required', 'integer', 'min:0', 'max:10000000', 'lte:subtotal'],
            'total' => ['required', 'integer', 'min:0', 'max:10000000'],
            'promo_code' => ['nullable', 'string', 'max:40'],
            'payment_reference' => ['nullable', 'string', 'max:1000'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ], [
            'customer_phone.regex' => 'Укажите телефон, например +7 700 123 45 67.',
            'copy.*.required' => 'Заполните все тексты приглашения.',
            'program_times.size' => 'Укажите время для трёх пунктов программы.',
        ]);

        DB::transaction(function () use ($data, $order, $request): void {
            $order = InvitationOrder::lockForUpdate()->findOrFail($order->id);
            $oldStatus = $order->status;
            $template = Template::findOrFail($data['template_id']);
            $music = empty($data['music_id']) ? null : Music::findOrFail($data['music_id']);
            $restaurant = empty($data['restaurant_id']) ? null : Restaurant::findOrFail($data['restaurant_id']);
            $details = array_replace($order->details ?? [], [
                'event_type' => $data['event_type'],
                'names' => $data['names'],
                'hosts' => $data['hosts'],
                'event_date' => $data['event_date'],
                'event_time' => $data['event_time'],
                'restaurant_id' => $data['restaurant_id'] ?? null,
                'venue_name' => $data['venue_name'],
                'venue_address' => $data['venue_address'],
                'two_gis_url' => $restaurant?->two_gis_url,
                'language' => $data['language'],
                'invitation_text' => $data['invitation_text'] ?? '',
                'theme' => $template->config_json['theme'] ?? 'pearl',
                'template_name' => $template->name,
                'music_id' => $music?->id,
                'music_url' => $music?->audio_url,
                'music_name' => $music?->name,
                'program_times' => array_values($data['program_times']),
                'copy' => $data['copy'],
            ]);

            if ($oldStatus !== 'rejected' && $data['status'] === 'rejected' && $order->promo_code_id) {
                PromoCode::whereKey($order->promo_code_id)->where('uses', '>', 0)->decrement('uses');
            } elseif ($oldStatus === 'rejected' && $data['status'] !== 'rejected' && $order->promo_code_id) {
                PromoCode::whereKey($order->promo_code_id)->increment('uses');
            }

            $order->update([
                'template_id' => $template->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'details' => $details,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'total' => $data['total'],
                'promo_code' => $data['promo_code'] ?? null,
                'status' => $data['status'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'admin_note' => $data['admin_note'] ?? null,
                'paid_at' => $data['status'] === 'paid' ? ($order->paid_at ?? now()) : null,
                'confirmed_by' => $data['status'] === 'paid' ? $request->user()->id : null,
            ]);

            $this->syncInvitation($order, $details, $template, $data['status'] === 'paid');
            $this->syncRestaurantBonus($order->refresh());
        }, 3);

        return redirect()->route('admin.store.orders.edit', $order)->with('success', 'Заказ и приглашение обновлены.');
    }

    public function destroyOrder(InvitationOrder $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $order = InvitationOrder::with('invitation.event')->lockForUpdate()->findOrFail($order->id);
            if ($order->promo_code_id && $order->status !== 'rejected') {
                PromoCode::whereKey($order->promo_code_id)->where('uses', '>', 0)->decrement('uses');
            }
            $invitation = $order->invitation;
            $event = $invitation?->event;
            $order->delete();
            if ($event) {
                $event->delete();
            } else {
                $invitation?->delete();
            }
        }, 3);

        return redirect()->route('admin.store.index')->with('success', 'Заказ, приглашение и ответы гостей удалены.');
    }

    public function reject(Request $request, InvitationOrder $order): RedirectResponse
    {
        $data = $request->validate(['admin_note' => ['required', 'string', 'max:1000']], ['admin_note.required' => 'Укажите причину отклонения для клиента.']);
        DB::transaction(function () use ($order, $data) {
            $order = InvitationOrder::lockForUpdate()->findOrFail($order->id);
            if ($order->status === 'rejected') {
                return;
            }
            abort_unless(in_array($order->status, ['pending', 'review']), 409);
            if ($order->promo_code_id) {
                PromoCode::whereKey($order->promo_code_id)->where('uses', '>', 0)->decrement('uses');
            }
            $order->update(['status' => 'rejected', 'admin_note' => $data['admin_note']]);
        }, 3);

        return back()->with('success', 'Заказ отклонён. Резерв промокода освобождён.');
    }

    public function saveTemplate(Request $request, ?Template $template = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:120', Rule::unique('templates')->ignore($template?->id)],
            'event_type' => ['nullable', Rule::in(array_keys(config('store.event_types')))],
            'price' => ['required', 'integer', 'min:7990', 'max:10000000'],
            'theme' => ['required', Rule::in(array_keys(config('store.themes')))],
            'preview_image' => ['nullable', 'string', 'max:255', 'regex:/^(https:\/\/|\/)[^\s]+$/'],
            'preview_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:10240'],
            'content_title' => ['required', 'string', 'max:160'],
            'content_event_label' => ['required', 'string', 'max:120'],
            'content_intro_title' => ['required', 'string', 'max:300'],
            'content_invitation_text' => ['required', 'string', 'max:1500'],
            'content_event_date' => ['required', 'date'],
            'content_event_time' => ['required', 'date_format:H:i'],
            'content_date_title' => ['required', 'string', 'max:120'],
            'content_program_title' => ['required', 'string', 'max:120'],
            'content_welcome_text' => ['required', 'string', 'max:120'],
            'content_ceremony_text' => ['required', 'string', 'max:120'],
            'content_celebration_text' => ['required', 'string', 'max:120'],
            'content_venue_title' => ['required', 'string', 'max:120'],
            'content_venue_name' => ['required', 'string', 'max:160'],
            'content_venue_address' => ['required', 'string', 'max:255'],
            'content_countdown_title' => ['required', 'string', 'max:120'],
            'content_hosts_title' => ['required', 'string', 'max:120'],
            'content_hosts_name' => ['required', 'string', 'max:180'],
            'content_rsvp_title' => ['required', 'string', 'max:120'],
            'content_rsvp_hint' => ['required', 'string', 'max:300'],
            'content_closing_text' => ['required', 'string', 'max:180'],
        ]);

        $content = collect($data)
            ->filter(fn ($value, string $key) => str_starts_with($key, 'content_'))
            ->mapWithKeys(fn ($value, string $key) => [str_replace('content_', '', $key) => $value])
            ->all();
        $config = $template?->config_json ?? [];
        $config['theme'] = $data['theme'];
        $config['sample_names'] = $content['title'];
        $config['content_kk'] = $content;

        $previewImage = $data['preview_image'] ?? $template?->preview_image;
        if ($request->hasFile('preview_image_file')) {
            $file = $request->file('preview_image_file');
            $path = $file->storePubliclyAs('designs', Str::uuid().'.'.$file->extension(), 'public');
            $previewImage = '/storage/'.$path;
            if ($template?->preview_image && str_starts_with($template->preview_image, '/storage/designs/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $template->preview_image));
            }
        }

        $values = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'event_type' => $data['event_type'] ?? null,
            'category' => $data['event_type'] ?? 'all',
            'price' => $data['price'],
            'preview_image' => $previewImage,
            'config_json' => $config,
            'is_active' => $request->boolean('is_active'),
        ];
        if ($template) {
            $template->update($values);
        } else {
            Template::create($values);
        }

        return back()->with('success', 'Дизайн сохранён.');
    }

    public function saveMusic(Request $request, ?Music $music = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'string', Rule::in(array_keys(config('store.music_categories')))],
            'audio_file' => [$music ? 'nullable' : 'required', 'file', 'mimes:mp3,m4a,mp4,wav,ogg,webm', 'max:30720'],
        ], [
            'categories.required' => 'Выберите хотя бы одну категорию.',
            'audio_file.required' => 'Выберите аудиофайл на компьютере.',
            'audio_file.mimes' => 'Поддерживаются MP3, M4A, MP4 Audio, WAV, OGG и WebM.',
            'audio_file.max' => 'Размер аудиофайла не должен превышать 30 МБ.',
        ]);
        $categories = array_values(array_unique($data['categories']));
        $values = [
            'name' => $data['name'],
            'categories' => $categories,
            'category' => collect($categories)->map(fn (string $category) => config('store.music_categories.'.$category))->join(', '),
            'is_active' => $request->boolean('is_active'),
        ];
        $oldAudioUrl = $music?->audio_url;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $path = $file->storePubliclyAs('music', Str::uuid().'.'.$file->extension(), 'public');
            $values['audio_url'] = '/media/'.$path;
        }
        if ($music) {
            $music->update($values);
        } else {
            Music::create($values);
        }
        if (isset($values['audio_url']) && $oldAudioUrl && (str_starts_with($oldAudioUrl, '/storage/music/') || str_starts_with($oldAudioUrl, '/media/music/'))) {
            Storage::disk('public')->delete(preg_replace('#^/(?:storage|media)/#', '', $oldAudioUrl));
        }

        return back()->with('success', 'Музыка сохранена.');
    }

    public function savePromo(Request $request, ?PromoCode $promo = null): RedirectResponse
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $data = $request->validate([
            'code' => ['nullable', 'regex:/^[A-Z0-9-]+$/', 'max:40', Rule::unique('promo_codes')->ignore($promo?->id)],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'integer', 'min:1', 'max:'.($request->input('type') === 'percent' ? '100' : '10000000')],
            'max_uses' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'expires_at' => ['nullable', 'date'],
        ]);
        $data['code'] = $data['code'] ?: 'ZHAR-'.strtoupper(Str::random(10));
        $data['is_active'] = $request->boolean('is_active');
        if ($promo) {
            $promo->update($data);
        } else {
            PromoCode::create($data);
        }

        return back()->with('success', 'Промокод '.$data['code'].' сохранён.');
    }

    public function saveRestaurant(Request $request, ?Restaurant $restaurant = null): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'city' => ['required', 'string', 'max:100'], 'address' => ['required', 'string', 'max:255'], 'two_gis_url' => ['nullable', 'url:http,https', 'max:500'], 'phone' => ['nullable', 'string', 'max:30'], 'bonus_percent' => ['nullable', 'numeric', 'min:0', 'max:100']]);
        $data['bonus_percent'] = $data['bonus_percent'] ?? 0;
        $data['status'] = $request->boolean('is_active') ? 'active' : 'inactive';
        if ($restaurant) {
            $restaurant->update($data);
        } else {
            Restaurant::create($data);
        }

        return back()->with('success', 'Ресторан сохранён в каталоге.');
    }

    private function syncRestaurantBonus(InvitationOrder $order): void
    {
        $existing = BonusTransaction::where('invitation_order_id', $order->id)->first();
        $restaurantId = data_get($order->details, 'restaurant_id');
        $restaurant = $restaurantId ? Restaurant::find($restaurantId) : null;

        if ($order->status !== 'paid' || ! $restaurant || (float) $restaurant->bonus_percent <= 0 || (int) $order->total <= 0) {
            $existing?->delete();

            return;
        }

        $amount = round(((int) $order->total * (float) $restaurant->bonus_percent) / 100, 2);
        if ($amount <= 0) {
            $existing?->delete();

            return;
        }

        BonusTransaction::updateOrCreate(
            ['invitation_order_id' => $order->id],
            [
                'restaurant_id' => $restaurant->id,
                'invitation_id' => $order->invitation_id,
                'amount' => $amount,
                'type' => 'accrual',
                'status' => 'available',
                'note' => 'Бонус '.$restaurant->bonus_percent.'% за заказ №'.$order->id,
            ],
        );
    }

    private function syncInvitation(InvitationOrder $order, array $details, Template $template, bool $published): void
    {
        $invitation = $order->invitation()->with('event')->first();
        $eventValues = [
            'restaurant_id' => $details['restaurant_id'] ?? null,
            'event_type' => $details['event_type'],
            'title' => $details['names'],
            'event_date' => $details['event_date'],
            'event_time' => $details['event_time'],
            'venue_name' => $details['venue_name'],
            'venue_address' => $details['venue_address'],
            'language' => $details['language'],
            'status' => $published ? 'active' : 'draft',
        ];

        if (! $invitation && ! $published) {
            return;
        }
        if (! $invitation) {
            $event = Event::create(['user_id' => null, ...$eventValues]);
            $invitation = Invitation::create([
                'event_id' => $event->id,
                'template_id' => $template->id,
                'slug' => $this->invitationSlug($details['names']),
                'content_json' => $details,
                'settings_json' => ['music_url' => $details['music_url'] ?? null, 'theme' => $details['theme']],
                'status' => 'published',
                'published_at' => now(),
            ]);
            $order->update(['invitation_id' => $invitation->id]);

            return;
        }

        $invitation->event->update($eventValues);
        $invitation->update([
            'template_id' => $template->id,
            'content_json' => $details,
            'settings_json' => ['music_url' => $details['music_url'] ?? null, 'theme' => $details['theme']],
            'status' => $published ? 'published' : 'draft',
            'published_at' => $published ? ($invitation->published_at ?? now()) : null,
        ]);
    }

    private function invitationCopyDefaults(string $language, string $eventType): array
    {
        if ($language === 'ru') {
            return [
                'event_label' => 'ТОРЖЕСТВО', 'intro' => 'ДОРОГИЕ РОДНЫЕ И ДРУЗЬЯ!', 'date_title' => 'Дата торжества',
                'program' => 'Программа вечера', 'welcome' => 'Сбор гостей', 'ceremony' => 'Торжественная церемония',
                'celebration' => 'Праздничный вечер', 'venue' => 'Место проведения', 'map' => 'Посмотреть на карте',
                'countdown' => 'До торжества', 'days' => 'дней', 'hours' => 'часов', 'minutes' => 'минут', 'seconds' => 'секунд',
                'hosts' => 'Хозяева торжества', 'rsvp' => 'Будем ждать вас!', 'hint' => 'Пожалуйста, сообщите, сможете ли вы прийти.',
                'name' => 'Ваше имя', 'answer' => 'Вы придёте?', 'yes' => 'С удовольствием приду', 'no' => 'К сожалению, не смогу',
                'maybe' => 'Сообщу позже', 'count' => 'Количество гостей', 'message' => 'Ваше пожелание', 'send' => 'Отправить ответ',
                'closing' => 'Разделите с нами этот счастливый день!',
            ];
        }

        return [
            'event_label' => match ($eventType) {
                'qyz_uzatu' => 'ҚЫЗ ҰЗАТУ', 'anniversary' => 'МЕРЕЙТОЙ', 'birthday' => 'ТУҒАН КҮН', default => 'ҮЙЛЕНУ ТОЙЫ'
            },
            'intro' => 'ҚҰРМЕТТІ АҒАЙЫН-ТУЫС, БАУЫРЛАР, ҚҰДА-ЖЕКЖАТ, ДОС-ЖАРАНДАР!', 'date_title' => 'Той салтанаты',
            'program' => 'Той бағдарламасы', 'welcome' => 'Қонақтардың жиналуы', 'ceremony' => $eventType === 'qyz_uzatu' ? 'Қыз ұзату рәсімі' : 'Салтанатты рәсім',
            'celebration' => 'Мерекелік кеш', 'venue' => 'Мекенжайымыз', 'map' => 'Картадан көру', 'countdown' => 'Салтанатқа дейін',
            'days' => 'күн', 'hours' => 'сағат', 'minutes' => 'минут', 'seconds' => 'секунд', 'hosts' => 'Той иелері',
            'rsvp' => 'Сізді күтеміз!', 'hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.', 'name' => 'Аты-жөніңіз',
            'answer' => 'Тойға қатысасыз ба?', 'yes' => 'Иә, қуана қатысамын', 'no' => 'Өкінішке қарай, қатыса алмаймын',
            'maybe' => 'Кейінірек айтамын', 'count' => 'Қонақ саны', 'message' => 'Ақ тілегіңіз', 'send' => 'Жауап жіберу',
            'closing' => 'Қуанышымызға ортақ болыңыз!',
        ];
    }

    private function invitationSlug(string $names): string
    {
        $letters = [
            'а' => 'a', 'ә' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ғ' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k',
            'қ' => 'q', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'ң' => 'n', 'о' => 'o', 'ө' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ұ' => 'u', 'ү' => 'u',
            'ф' => 'f', 'х' => 'h', 'һ' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh',
            'ы' => 'y', 'і' => 'i', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ь' => '', 'ъ' => '',
        ];
        $namePart = Str::slug(strtr(mb_strtolower($names), $letters));
        $namePart = trim(substr($namePart ?: 'shaqyru', 0, 80), '-');

        do {
            $slug = $namePart.'-'.random_int(10000, 99999);
        } while (Invitation::where('slug', $slug)->exists());

        return $slug;
    }
}

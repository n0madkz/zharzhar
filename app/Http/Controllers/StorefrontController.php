<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationOrderRequest;
use App\Models\Invitation;
use App\Models\InvitationOrder;
use App\Models\Music;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('event')->toString();
        $templates = Template::where('is_active', true)->when(array_key_exists($category, config('store.event_types')), fn ($query) => $query->where(fn ($q) => $q->where('event_type', $category)->orWhereNull('event_type')))->orderBy('price')->get();

        return view('store.catalog', compact('templates', 'category'));
    }

    public function preview(Template $template): View
    {
        abort_unless($template->is_active, 404);

        return view('store.invitation', ['template' => $template, 'preview' => true, 'invitation' => null,
            'details' => ['names' => 'Алихан & Аружан', 'hosts' => 'Ерлан – Айгүл', 'event_type' => $template->event_type ?? 'wedding', 'event_date' => now()->addMonth()->format('Y-m-d'), 'event_time' => '18:00', 'venue_name' => 'Ваш ресторан', 'venue_address' => 'Город и адрес вашего торжества', 'language' => 'ru', 'invitation_text' => 'Приглашаем вас разделить с нами радость этого особенного дня!', 'theme' => $template->config_json['theme'] ?? 'sage']]);
    }

    public function checkout(Request $request, Template $template): View
    {
        abort_unless($template->is_active, 404);
        $key = (string) Str::uuid();
        $request->session()->put('checkout_keys.'.$key, true);

        return view('store.checkout', [
            'template' => $template, 'requestKey' => $key,
            'restaurants' => Restaurant::where('status', 'active')->orderBy('name')->get(),
            'music' => Music::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate(['template_id' => ['required', 'integer'], 'promo_code' => ['nullable', 'string', 'max:40']]);
        $template = Template::where('is_active', true)->findOrFail($data['template_id']);
        $promo = $this->promo($data['promo_code'] ?? '');
        $discount = $promo?->discountFor($template->price) ?? 0;

        return response()->json(['subtotal' => $template->price, 'discount' => $discount, 'total' => $template->price - $discount]);
    }

    private function promo(string $code, bool $lock = false): ?PromoCode
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return null;
        }
        $promo = PromoCode::where('code', $code)->when($lock, fn ($q) => $q->lockForUpdate())->first();
        if (! $promo) {
            throw ValidationException::withMessages(['promo_code' => 'Промокод не найден.']);
        }

        return $promo;
    }

    public function store(StoreInvitationOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        abort_unless($request->session()->has('checkout_keys.'.$data['request_key']), 419);
        $order = DB::transaction(function () use ($data) {
            $template = Template::where('is_active', true)->lockForUpdate()->findOrFail($data['template_id']);
            $existing = InvitationOrder::where('request_key', $data['request_key'])->first();
            if ($existing) {
                return $existing;
            }
            $promo = $this->promo($data['promo_code'] ?? '', true);
            $discount = $promo?->discountFor($template->price) ?? 0;
            if ($template->event_type && $template->event_type !== $data['event_type']) {
                throw ValidationException::withMessages(['event_type' => 'Этот дизайн предназначен для другого события.']);
            }
            $music = empty($data['music_id']) ? null : Music::where('is_active', true)->findOrFail($data['music_id']);
            $details = collect($data)->only(['event_type', 'names', 'hosts', 'event_date', 'event_time', 'restaurant_id', 'venue_name', 'venue_address', 'language', 'invitation_text'])->all();
            $details['theme'] = $template->config_json['theme'] ?? 'sage';
            $details['template_name'] = $template->name;
            $details['music_url'] = $music?->audio_url;
            $details['music_name'] = $music?->name;
            $order = InvitationOrder::create([
                'template_id' => $template->id, 'promo_code_id' => $promo?->id,
                'token' => Str::random(64), 'responses_token' => Str::random(64), 'request_key' => $data['request_key'],
                'customer_name' => $data['customer_name'], 'customer_phone' => $data['customer_phone'],
                'details' => $details, 'subtotal' => $template->price, 'discount' => $discount,
                'total' => $template->price - $discount, 'promo_code' => $promo?->code,
            ]);
            $promo?->increment('uses');

            return $order;
        }, 3);

        return redirect()->route('store.payment', $order->token);
    }

    public function payment(string $token): Response
    {
        $order = InvitationOrder::with('invitation')->where('token', $token)->firstOrFail();

        return $this->privateView('store.payment', compact('order'));
    }

    public function submitPayment(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate(['payment_reference' => ['required', 'string', 'max:500']], ['payment_reference.required' => 'Укажите имя отправителя и время перевода.']);
        InvitationOrder::where('token', $token)->firstOrFail();
        InvitationOrder::where('token', $token)->where('status', 'pending')->update([
            'status' => 'review', 'payment_reference' => $data['payment_reference'], 'submitted_at' => now(),
        ]);

        return back()->with('success', 'Информация отправлена. Обновите эту страницу после проверки оплаты.');
    }

    public function invitation(string $slug): View
    {
        $invitation = Invitation::with('template')->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $order = InvitationOrder::where('invitation_id', $invitation->id)->where('status', 'paid')->firstOrFail();

        return view('store.invitation', ['invitation' => $invitation, 'template' => $invitation->template, 'details' => $order->details, 'preview' => false]);
    }

    public function rsvp(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::where('slug', $slug)->where('status', 'published')->firstOrFail();
        abort_unless(InvitationOrder::where('invitation_id', $invitation->id)->where('status', 'paid')->exists(), 404);
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'attendance_status' => ['required', 'in:yes,no,maybe'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:20'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
        if ($data['attendance_status'] === 'no') {
            $data['guest_count'] = 0;
        }
        $key = 'rsvp.'.$invitation->id;
        $existing = $request->session()->get($key);
        $response = $invitation->rsvps()->updateOrCreate(['id' => $existing], $data);
        $request->session()->put($key, $response->id);

        return back()->with('success', ($invitation->content_json['language'] ?? 'ru') === 'kk' ? 'Жауабыңыз сақталды. Рақмет!' : 'Спасибо! Ваш ответ сохранён.');
    }

    public function responses(string $token): Response
    {
        $order = InvitationOrder::with('invitation')->where('responses_token', $token)->where('status', 'paid')->firstOrFail();
        $rsvps = $order->invitation->rsvps()->latest()->paginate(50);
        $counts = [
            'yes' => $order->invitation->rsvps()->where('attendance_status', 'yes')->sum('guest_count'),
            'no' => $order->invitation->rsvps()->where('attendance_status', 'no')->count(),
            'maybe' => $order->invitation->rsvps()->where('attendance_status', 'maybe')->count(),
        ];

        return $this->privateView('store.responses', compact('order', 'rsvps', 'counts'));
    }

    private function privateView(string $view, array $data): Response
    {
        return response()->view($view, $data)->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}

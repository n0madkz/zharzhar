<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invitation;
use App\Models\InvitationOrder;
use App\Models\Music;
use App\Models\PromoCode;
use App\Models\Restaurant;
use App\Models\Template;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $orders = InvitationOrder::with('invitation')->when(in_array($status, ['pending', 'review', 'paid', 'rejected']), fn ($q) => $q->where('status', $status))->latest()->paginate(20)->withQueryString();

        return view('admin.store', [
            'orders' => $orders, 'status' => $status,
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
                'event_id' => $event->id, 'template_id' => $order->template_id, 'slug' => strtolower(Str::random(24)),
                'content_json' => $details, 'settings_json' => ['music_url' => $details['music_url'] ?? null, 'theme' => $details['theme']],
                'status' => 'published', 'published_at' => now(),
            ]);
            $order->update(['status' => 'paid', 'paid_at' => now(), 'confirmed_by' => $request->user()->id, 'invitation_id' => $invitation->id, 'admin_note' => $data['admin_note'] ?? null]);
        }, 3);

        return back()->with('success', 'Оплата подтверждена. Обе ссылки созданы и доступны клиенту на странице заказа.');
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
            'preview_image' => ['nullable', 'url:https', 'max:255'],
        ]);
        $values = [...collect($data)->except('theme')->all(), 'category' => $data['event_type'] ?? 'all', 'config_json' => ['theme' => $data['theme']], 'is_active' => $request->boolean('is_active')];
        if ($template) {
            $template->update($values);
        } else {
            Template::create($values);
        }

        return back()->with('success', 'Дизайн сохранён.');
    }

    public function saveMusic(Request $request, ?Music $music = null): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'category' => ['required', 'string', 'max:80'], 'audio_url' => ['required', 'url:https', 'max:255']]);
        $data['is_active'] = $request->boolean('is_active');
        if ($music) {
            $music->update($data);
        } else {
            Music::create($data);
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
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'city' => ['required', 'string', 'max:100'], 'address' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:30']]);
        $data['status'] = $request->boolean('is_active') ? 'active' : 'inactive';
        if ($restaurant) {
            $restaurant->update($data);
        } else {
            Restaurant::create($data);
        }

        return back()->with('success', 'Ресторан сохранён в каталоге.');
    }
}

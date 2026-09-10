<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Carbon\Carbon;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant()->with('slots')->firstOrFail();
        $selectedDate = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $month = $request->month ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth() : $selectedDate->copy()->startOfMonth();
        $calendarStart = $month->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $calendarDays = [];
        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $calendarDays[] = $day->copy();
        }
        $bookings = Booking::where('restaurant_id', $restaurant->id)->with('slot')->whereBetween('booking_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])->latest('booking_date')->latest()->get();
        $allBookings = $bookings;
        $bookings = $bookings->filter(fn (Booking $booking) => $booking->booking_date->isSameDay($selectedDate));
        $supportMessages = $bookings->mapWithKeys(fn (Booking $booking) => [
            $booking->id => $this->supportMessage($restaurant, $booking),
        ]);
        $selectedBookings = $bookings->filter(fn (Booking $booking) => $booking->booking_date->isSameDay($selectedDate));
        $calendarBookingData = $allBookings->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'date' => $booking->booking_date->format('Y-m-d'),
            'name' => $booking->visitor_name,
            'eventType' => $this->eventTypeLabel($booking->event_type),
            'slotId' => $booking->restaurant_slot_id,
            'slot' => $this->slotLabel($booking->slot),
            'status' => $booking->status,
            'statusLabel' => $booking->statusLabel(),
            'guests' => $booking->guest_count,
            'pricePerGuest' => (float) $booking->price_per_guest,
            'prepayment' => (float) $booking->prepayment,
            'phone' => $booking->phone,
            'note' => $booking->note,
            'whatsapp' => 'https://wa.me/77067160199?text='.rawurlencode($this->supportMessage($restaurant, $booking)),
        ])->values();
        $reportPeriod = $request->input('report_period', 'all');
        $reportFrom = $request->input('report_from');
        $reportTo = $request->input('report_to');
        $reportBookings = Booking::where('restaurant_id', $restaurant->id)->with('slot')
            ->when($reportFrom, fn ($query) => $query->whereDate('booking_date', '>=', $reportFrom))
            ->when($reportTo, fn ($query) => $query->whereDate('booking_date', '<=', $reportTo))
            ->latest('booking_date')->latest()->paginate(10)->withQueryString();
        $reportRows = $reportBookings->map(fn (Booking $booking) => [
            'date' => $booking->booking_date->format('d.m.Y'), 'event' => $booking->event_type, 'name' => $booking->visitor_name,
            'slot' => $this->slotLabel($booking->slot), 'guests' => $booking->guest_count, 'total' => number_format($booking->total_amount, 2, ',', ' ').' ₸', 'status' => $booking->statusLabel(),
        ])->values();
        $reportPeriods = $restaurant->slots->map(fn ($slot) => ['key' => $slot->slot_key, 'label' => $this->slotLabel($slot)])->values();

        return view('restaurant.framework', compact('restaurant', 'bookings', 'allBookings', 'selectedBookings', 'selectedDate', 'month', 'calendarDays', 'supportMessages', 'calendarBookingData', 'reportBookings', 'reportPeriod', 'reportFrom', 'reportTo', 'reportRows', 'reportPeriods'));
    }

    public function exportReports(Request $request): mixed
    {
        $restaurant = $request->user()->restaurant()->firstOrFail();
        $period = $request->input('report_period', 'all');
        $from = $request->input('report_from');
        $to = $request->input('report_to');
        $bookings = Booking::where('restaurant_id', $restaurant->id)->with('slot')
            ->when($from, fn ($query) => $query->whereDate('booking_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('booking_date', '<=', $to))
            ->orderBy('booking_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($bookings): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Ресторан', 'Дата', 'Тип мероприятия', 'Имя посетителя', 'Телефон', 'Количество гостей', 'Цена за 1 гостя', 'Предоплата', 'Итого', 'Период', 'Начало', 'Конец', 'Статус', 'Примечания', 'Создано'], ';');
            foreach ($bookings as $booking) {
                fputcsv($out, [$booking->id, $restaurant->name, $booking->booking_date->format('d.m.Y'), $booking->event_type, $booking->visitor_name, $booking->phone, $booking->guest_count, $booking->price_per_guest, $booking->prepayment, $booking->total_amount, $booking->slot?->label, $booking->slot?->start_time, $booking->slot?->end_time, $booking->statusLabel(), $booking->note, $booking->created_at?->format('d.m.Y H:i')], ';');
            }
            fclose($out);
        }, 'zharzhar-bookings.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function calendarData(Request $request): mixed
    {
        $data = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $restaurant = $request->user()->restaurant()->with('slots')->firstOrFail();
        $month = isset($data['month']) ? Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth() : Carbon::today()->startOfMonth();
        $start = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $bookings = Booking::where('restaurant_id', $restaurant->id)->with('slot')
            ->whereBetween('booking_date', [$start->toDateString(), $end->toDateString()])->get();
        $days = [];
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $days[] = ['date' => $day->format('Y-m-d'), 'day' => $day->day, 'inMonth' => $day->month === $month->month];
        }

        return response()->json([
            'month' => $month->locale(app()->getLocale())->translatedFormat('F Y'),
            'value' => $month->format('Y-m'),
            'days' => $days,
            'slots' => $restaurant->slots->map(fn ($slot) => ['id' => $slot->id, 'label' => $this->slotLabel($slot), 'color' => $slot->color]),
            'bookings' => $bookings->map(fn (Booking $booking) => [
                'id' => $booking->id, 'date' => $booking->booking_date->format('Y-m-d'), 'name' => $booking->visitor_name,
                'eventType' => $this->eventTypeLabel($booking->event_type), 'slotId' => $booking->restaurant_slot_id, 'slot' => $this->slotLabel($booking->slot),
                'status' => $booking->status, 'statusLabel' => $booking->statusLabel(), 'guests' => $booking->guest_count, 'pricePerGuest' => (float) $booking->price_per_guest,
                'prepayment' => (float) $booking->prepayment, 'phone' => $booking->phone, 'note' => $booking->note,
                'whatsapp' => 'https://wa.me/77067160199?text='.rawurlencode($this->supportMessage($restaurant, $booking)),
            ])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant()->with('slots')->firstOrFail();
        $data = $request->validate([
            'visitor_name' => ['required', 'string', 'max:120'],
            'event_type' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'booking_date' => ['required', 'date'],
            'restaurant_slot_id' => ['required', 'integer', 'exists:restaurant_slots,id'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'price_per_guest' => ['required', 'numeric', 'min:0'],
            'prepayment' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        abort_unless($restaurant->slots->contains('id', (int) $data['restaurant_slot_id']), 422, __('partner.messages.slot_foreign'));
        $data['prepayment'] = $data['prepayment'] ?? 0;
        if ((float) $data['prepayment'] > ((float) $data['price_per_guest'] * (int) $data['guest_count'])) {
            return back()->withInput()->withErrors(['prepayment' => __('partner.messages.prepayment_high')]);
        }
        $occupied = Booking::where('restaurant_id', $restaurant->id)->whereDate('booking_date', $data['booking_date'])->where('restaurant_slot_id', $data['restaurant_slot_id'])->where('status', '!=', 'cancelled')->exists();
        if ($occupied) {
            return back()->withInput()->withErrors(['restaurant_slot_id' => __('partner.messages.slot_occupied')]);
        }
        $slot = $restaurant->slots->firstWhere('id', (int) $data['restaurant_slot_id']);
        $booking = Booking::create([...$data, 'restaurant_id' => $restaurant->id, 'color' => $slot->color, 'status' => 'pending']);

        return redirect()->to(route('restaurant.dashboard', [
            'month' => Carbon::parse($booking->booking_date)->format('Y-m'),
            'date' => Carbon::parse($booking->booking_date)->format('Y-m-d'),
        ]).'#schedule')->with('success', __('partner.messages.booking_added'));
    }

    public function updateSlots(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant()->with('slots')->firstOrFail();
        $data = $request->validate([
            'slots' => ['required', 'array'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
            'slots.*.end_time' => ['required', 'date_format:H:i'],
            'default_price_per_guest' => ['required', 'numeric', 'min:0'],
        ]);
        $restaurant->update(['default_price_per_guest' => $data['default_price_per_guest']]);
        DB::transaction(function () use ($restaurant, $data): void {
            foreach ($restaurant->slots as $slot) {
                $values = $data['slots'][$slot->slot_key] ?? null;
                if ($values === null) {
                    continue;
                }

                // Обновляем только период текущего ресторана.
                $restaurant->slots()->whereKey($slot->id)->update([
                    'start_time' => $values['start_time'],
                    'end_time' => $values['end_time'],
                ]);
            }
        });

        return redirect()->to(route('restaurant.dashboard').'#settings')->with('success', __('partner.messages.settings_saved'));
    }

    public function updateLanguage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'preferred_language' => ['required', 'in:kk,ru,en'],
        ]);

        $request->user()->update(['preferred_language' => $data['preferred_language']]);
        app()->setLocale($data['preferred_language']);
        Carbon::setLocale($data['preferred_language']);

        return redirect()->to(route('restaurant.dashboard').'#settings')
            ->with('success', __('partner.messages.language_saved'));
    }

    public function updateBooking(Request $request, Booking $booking): RedirectResponse
    {
        $restaurant = $request->user()->restaurant()->with('slots')->firstOrFail();
        abort_unless($booking->restaurant_id === $restaurant->id, 404);

        $data = $request->validate([
            'visitor_name' => ['required', 'string', 'max:120'],
            'event_type' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'booking_date' => ['required', 'date'],
            'restaurant_slot_id' => ['required', 'integer', 'exists:restaurant_slots,id'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'price_per_guest' => ['required', 'numeric', 'min:0'],
            'prepayment' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,confirmed,cancelled'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $data['prepayment'] = $data['prepayment'] ?? 0;
        if ((float) $data['prepayment'] > ((float) $data['price_per_guest'] * (int) $data['guest_count'])) {
            return back()->withInput()->withErrors(['prepayment' => __('partner.messages.prepayment_high')]);
        }
        abort_unless($restaurant->slots->contains('id', (int) $data['restaurant_slot_id']), 422);

        $occupied = Booking::where('restaurant_id', $restaurant->id)
            ->whereDate('booking_date', $data['booking_date'])
            ->where('restaurant_slot_id', $data['restaurant_slot_id'])
            ->where('id', '!=', $booking->id)
            ->where('status', '!=', 'cancelled')
            ->exists();
        if ($occupied) {
            return back()->withInput()->withErrors(['restaurant_slot_id' => __('partner.messages.slot_occupied')]);
        }

        $slot = $restaurant->slots->firstWhere('id', (int) $data['restaurant_slot_id']);
        $booking->update([...$data, 'color' => $slot->color]);

        return redirect()->to(route('restaurant.dashboard', ['date' => $data['booking_date']]).'#booking-'.$booking->id)->with('success', __('partner.messages.booking_updated'));
    }

    public function qr(Request $request, Booking $booking): mixed
    {
        $restaurant = $request->user()->restaurant()->firstOrFail();
        abort_unless($booking->restaurant_id === $restaurant->id, 404);

        $message = $this->supportMessage($restaurant, $booking);
        $whatsappUrl = 'https://wa.me/77067160199?text='.rawurlencode($message);
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 8,
            'imageTransparent' => false,
        ]);
        $png = (new QRCode($options))->render($whatsappUrl);

        return response($png, 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=300']);
    }

    private function qrDataUrl($restaurant, Booking $booking): string
    {
        $message = $this->supportMessage($restaurant, $booking);
        $whatsappUrl = 'https://wa.me/77067160199?text='.rawurlencode($message);
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 8,
            'imageTransparent' => false,
        ]);

        return 'data:image/png;base64,'.base64_encode((new QRCode($options))->render($whatsappUrl));
    }

    private function qrSvg($restaurant, Booking $booking): string
    {
        $message = $this->supportMessage($restaurant, $booking);
        $whatsappUrl = 'https://wa.me/77067160199?text='.rawurlencode($message);
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'addQuietzone' => true,
        ]);

        $svg = (new QRCode($options))->render($whatsappUrl);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function supportMessage($restaurant, Booking $booking): string
    {
        $slot = $booking->slot;
        $period = $slot
            ? $slot->label.' ('.$slot->start_time.'–'.$slot->end_time.')'
            : 'көрсетілмеген';

        return implode("\n", [
            'Сәлеметсіз бе! Мен онлайн шақыру каталогтарын көргім келеді.',
            '',
            'Мейрамхана: '.$restaurant->name,
            'Қонақтың аты-жөні: '.$booking->visitor_name,
            'Іс-шара түрі: '.$booking->event_type,
            'Телефон: '.($booking->phone ?: 'көрсетілмеген'),
            'Күні: '.$booking->booking_date->format('d.m.Y'),
        ]);
    }

    public function destroyBooking(Request $request, Booking $booking): RedirectResponse
    {
        $restaurant = $request->user()->restaurant()->firstOrFail();
        abort_unless($booking->restaurant_id === $restaurant->id, 404);
        $booking->delete();

        return back()->with('success', __('partner.messages.booking_deleted'));
    }

    private function slotLabel($slot): string
    {
        if ($slot === null) {
            return __('partner.booking.not_selected');
        }

        $key = 'partner.slots.'.$slot->slot_key;
        $translated = __($key);

        return $translated === $key ? $slot->label : $translated;
    }

    private function eventTypeLabel(?string $eventType): string
    {
        if (! $eventType) {
            return __('partner.booking.not_specified');
        }

        $key = 'partner.event_types.'.$eventType;
        $translated = __($key);

        return $translated === $key ? $eventType : $translated;
    }
}

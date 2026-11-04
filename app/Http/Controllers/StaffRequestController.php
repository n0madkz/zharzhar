<?php

namespace App\Http\Controllers;

use App\Models\StaffApplication;
use App\Models\StaffRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffRequestController extends Controller
{
    public function index(): View
    {
        $requests = StaffRequest::query()
            ->with('restaurant:id,name,city')
            ->withCount(['applications as accepted_count' => fn ($query) => $query->where('status', 'accepted')])
            ->where('status', 'open')
            ->whereDate('event_date', '>=', today()->toDateString())
            ->orderBy('event_date')
            ->paginate(20);

        return view('staff.requests', compact('requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant()->firstOrFail();
        $data = $request->validate([
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'different:starts_at'],
            'workers_needed' => ['required', 'integer', 'between:1,100'],
            'pay_per_worker' => ['required', 'integer', 'between:1000,1000000'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        $restaurant->staffRequests()->create($data);

        return redirect()->to(route('restaurant.dashboard').'#staff')->with('success', 'Заявка на официантов опубликована.');
    }

    public function close(Request $request, StaffRequest $staffRequest): RedirectResponse
    {
        $this->ensureOwner($request, $staffRequest);
        $staffRequest->update(['status' => 'closed']);

        return redirect()->to(route('restaurant.dashboard').'#staff')->with('success', 'Заявка закрыта.');
    }

    public function apply(Request $request, StaffRequest $staffRequest): RedirectResponse
    {
        abort_unless($staffRequest->status === 'open' && $staffRequest->event_date->gte(today()), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);
        $phone = preg_replace('/\D+/', '', $data['phone']);
        if (strlen($phone) === 11 && str_starts_with($phone, '8')) {
            $phone = '7'.substr($phone, 1);
        }
        if (strlen($phone) === 10) {
            $phone = '7'.$phone;
        }
        if (! preg_match('/^\d{10,15}$/', $phone)) {
            throw ValidationException::withMessages(['phone' => 'Укажите действующий номер телефона.']);
        }
        if ($staffRequest->applications()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages(['phone' => 'Вы уже откликнулись на эту заявку.']);
        }
        $staffRequest->applications()->create([
            'name' => $data['name'],
            'phone' => $phone,
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('success', 'Отклик отправлен ресторану.');
    }

    public function updateApplication(Request $request, StaffRequest $staffRequest, StaffApplication $application): RedirectResponse
    {
        $this->ensureOwner($request, $staffRequest);
        abort_unless($application->staff_request_id === $staffRequest->id, 404);
        $data = $request->validate(['status' => ['required', Rule::in(['accepted', 'rejected'])]]);
        DB::transaction(function () use ($staffRequest, $application, $data): void {
            $locked = StaffRequest::query()->lockForUpdate()->findOrFail($staffRequest->id);
            if ($data['status'] === 'accepted' && $application->status !== 'accepted') {
                $accepted = $locked->applications()->where('status', 'accepted')->count();
                if ($locked->status !== 'open' || $accepted >= $locked->workers_needed) {
                    throw ValidationException::withMessages(['status' => 'Свободных мест в заявке больше нет.']);
                }
            }
            $application->update($data);
            if ($data['status'] === 'accepted' && $locked->applications()->where('status', 'accepted')->count() >= $locked->workers_needed) {
                $locked->update(['status' => 'closed']);
            }
        });

        return redirect()->to(route('restaurant.dashboard').'#staff')->with('success', 'Статус отклика обновлён.');
    }

    private function ensureOwner(Request $request, StaffRequest $staffRequest): void
    {
        abort_unless($staffRequest->restaurant_id === $request->user()->restaurant()->firstOrFail()->id, 404);
    }
}

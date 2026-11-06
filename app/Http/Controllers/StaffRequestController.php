<?php

namespace App\Http\Controllers;

use App\Models\StaffApplication;
use App\Models\StaffRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffRequestController extends Controller
{
    public function index(Request $request): View
    {
        $staffEnabled = $this->staffEnabled();
        $requests = $staffEnabled ? StaffRequest::query()
            ->with('restaurant:id,name,city')
            ->withCount(['applications as accepted_count' => fn ($query) => $query->where('status', 'accepted')])
            ->where('status', 'open')
            ->whereDate('event_date', '>=', today()->toDateString())
            ->orderBy('event_date')
            ->paginate(20) : new LengthAwarePaginator([], 0, 20, $request->integer('page', 1), ['path' => $request->url()]);
        $myApplications = $staffEnabled && $request->user()?->isRole('staff')
            ? $request->user()->staffApplications()->with('staffRequest.restaurant:id,name,city')->latest()->get()
            : collect();

        return view('staff.requests', compact('requests', 'myApplications', 'staffEnabled'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireStaffTables();
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
        $this->requireStaffTables();
        $this->ensureOwner($request, $staffRequest);
        $staffRequest->update(['status' => 'closed']);

        return redirect()->to(route('restaurant.dashboard').'#staff')->with('success', 'Заявка закрыта.');
    }

    public function apply(Request $request, StaffRequest $staffRequest): RedirectResponse
    {
        $this->requireStaffTables();
        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);
        $phone = $request->user()->phone;
        if (! preg_match('/^\d{10,15}$/', (string) $phone)) {
            throw ValidationException::withMessages(['phone' => 'В профиле требуется действующий номер телефона.']);
        }
        DB::transaction(function () use ($request, $staffRequest, $phone, $data): void {
            $locked = StaffRequest::query()->lockForUpdate()->findOrFail($staffRequest->id);
            abort_unless($locked->status === 'open' && $locked->event_date->gte(today()), 404);
            if ($locked->applications()->where(function ($query) use ($request, $phone): void {
                $query->where('phone', $phone)->orWhere('user_id', $request->user()->id);
            })->exists()) {
                throw ValidationException::withMessages(['phone' => 'Вы уже откликнулись на эту заявку.']);
            }
            $locked->applications()->create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'phone' => $phone,
                'message' => $data['message'] ?? null,
            ]);
        });

        return back()->with('success', 'Отклик отправлен ресторану.');
    }

    public function updateApplication(Request $request, StaffRequest $staffRequest, StaffApplication $application): RedirectResponse
    {
        $this->requireStaffTables();
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

    private function staffEnabled(): bool
    {
        return Schema::hasTable('staff_requests') && Schema::hasTable('staff_applications')
            && Schema::hasColumn('staff_applications', 'user_id');
    }

    private function requireStaffTables(): void
    {
        abort_unless($this->staffEnabled(), 503, 'Раздел персонала временно недоступен. Требуется выполнить миграции базы данных.');
    }
}

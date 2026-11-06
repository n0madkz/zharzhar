<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showStaffRegistration(): View
    {
        return view('auth.staff-register');
    }

    public function registerStaff(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phone,
            'password' => $data['password'],
            'role' => 'staff',
            'status' => 'active',
        ]);
        Auth::login($staff);
        $request->session()->regenerate();

        return redirect()->route('staff.dashboard');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $email = strcasecmp($credentials['login'], config('store.admin_login')) === 0
            ? config('store.admin_email')
            : $credentials['login'];

        if (! Auth::attempt(['email' => $email, 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Неверный логин или пароль.'])->onlyInput('login');
        }
        $request->session()->regenerate();

        return redirect()->intended(match (strtolower((string) Auth::user()->role)) {
            'broker' => '/broker',
            'partner' => '/restaurant',
            'staff' => '/staff',
            default => '/admin/store',
        });
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

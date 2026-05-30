<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::channel('fleet')->warning('Failed login attempt', [
                'email' => $credentials['email'],
                'ip'    => $request->ip(),
            ]);
            throw ValidationException::withMessages([
                'email' => __('Nieprawidłowy email lub hasło.'),
            ]);
        }

        $request->session()->regenerate();

        Log::channel('fleet')->info('User logged in', [
            'user_id' => $request->user()->id,
            'email'   => $request->user()->email,
            'role'    => $request->user()->role->value,
            'ip'      => $request->ip(),
        ]);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = $request->user()?->id;

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::channel('fleet')->info('User logged out', ['user_id' => $userId]);

        return redirect()->route('login');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $pendingUser = \App\Models\User::query()
            ->where('email', $request->string('email'))
            ->first();

        if ($pendingUser && in_array($pendingUser->account_status, ['suspended', 'banned'], true)) {
            throw ValidationException::withMessages([
                'email' => 'This account is currently ' . $pendingUser->account_status . '. Please contact support.',
            ]);
        }

        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(
            $request->user()->isAdmin()
                ? route('admin.dashboard')
                : route('dashboard')
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

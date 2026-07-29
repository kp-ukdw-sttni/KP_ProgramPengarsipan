<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $request->authenticate();

        $request->session()->regenerate();

        // Log login action
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Login',
            'ip_address' => $request->ip(),
            'details' => "Pengguna '" . Auth::user()->name . "' berhasil masuk ke dalam sistem.",
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Logout',
                'ip_address' => $request->ip(),
                'details' => "Pengguna '{$user->name}' keluar dari sistem.",
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

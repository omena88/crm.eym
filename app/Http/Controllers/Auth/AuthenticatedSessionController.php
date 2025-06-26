<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Demo login logic
        if ($request->has('rol')) {
            $rol = $request->input('rol');
            $user = User::where('rol', $rol)->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('dashboard');
            }

            return back()->withErrors(['rol' => 'No se encontró un usuario con el rol especificado.']);
        }

        // Original login logic (fallback)
        $loginRequest = LoginRequest::createFrom($request);
        $loginRequest->authenticate();
        $request->session()->regenerate();
        return redirect()->route('dashboard');
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

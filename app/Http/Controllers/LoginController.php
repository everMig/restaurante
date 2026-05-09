<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return $this->redirectUser();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Intenta nuevamente en 1 minuto.',
            ]);
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($this->throttleKey($request));
            $request->session()->regenerate();
            return $this->redirectUser();
        }

        RateLimiter::hit($this->throttleKey($request), 60);

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    // Función auxiliar para redirigir inteligentemente
    protected function redirectUser()
    {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('dashboard'); // El jefe va al panel de control
        }
        
        // Cajeros y Meseros van directo al trabajo (POS)
        return redirect()->route('pos.index'); 
    }

    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check()
            ? redirect()->route('dashboard')
            : Inertia::render('auth/Login');
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $user = Auth::user();
        
        // Check if user's tenant allows web login
        if ($user->tenant && !$user->tenant->canWebLogin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This tenant is configured for API-only access. Web login is not permitted.',
            ]);
        }

        $request->session()->regenerate();
        
        session()->flash('login_success', [
            'message' => 'Hello, welcome to Iconic ' . $user->name,
            'user_name' => $user->name
        ]);
        
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

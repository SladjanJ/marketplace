<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('verification.notice')
                ->with('error', 'Account created, but the verification email could not be sent. Use Resend on this page or check mail settings.');
        }

        return redirect()
            ->route('verification.notice')
            ->with('success', 'Account created. Please check your email and click the verification link.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('error', 'Please verify your email before continuing.');
        }

        return redirect()->intended(route('ads.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ads.index')->with('success', 'You have been logged out.');
    }
}

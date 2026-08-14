<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\LocaleManager;
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
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $locale = $request->session()->get('locale_chosen')
            ? $request->session()->get('locale')
            : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
            'locale' => LocaleManager::isSupported($locale) ? $locale : null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('ads.index')
            ->with('success', __('ui.account_created'));
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
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if (LocaleManager::isSupported($user->locale)) {
            LocaleManager::apply($request, $user->locale);
        } elseif ($request->session()->get('locale_chosen') && LocaleManager::isSupported($request->session()->get('locale'))) {
            $user->update(['locale' => $request->session()->get('locale')]);
        }

        return redirect()->intended(route('ads.index'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ads.index')->with('success', __('ui.logged_out'));
    }
}

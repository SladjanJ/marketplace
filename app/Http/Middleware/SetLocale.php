<?php

namespace App\Http\Middleware;

use App\Support\LocaleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $userLocale = $request->user()?->locale;
        $sessionLocale = $request->session()->get('locale');
        $cookieLocale = $request->cookie(LocaleManager::COOKIE);

        $locale = LocaleManager::DEFAULT;

        if (LocaleManager::isSupported($userLocale)) {
            $locale = $userLocale;
        } elseif (LocaleManager::isSupported($sessionLocale)) {
            $locale = $sessionLocale;
        } elseif (LocaleManager::isSupported($cookieLocale)) {
            $locale = $cookieLocale;
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $chosen = (bool) $request->session()->get('locale_chosen')
            || $request->cookie(LocaleManager::CHOSEN_COOKIE)
            || LocaleManager::isSupported($userLocale);

        view()->share('needsLanguageChoice', ! $chosen);

        return $next($request);
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleManager
{
    public const SUPPORTED = ['en', 'sr'];

    public const DEFAULT = 'en';

    public const COOKIE = 'locale';

    public const CHOSEN_COOKIE = 'locale_chosen';

    public const COOKIE_MINUTES = 525600;

    public static function isSupported(?string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public static function apply(Request $request, string $locale): void
    {
        $locale = self::isSupported($locale) ? $locale : self::DEFAULT;

        $request->session()->put('locale', $locale);
        $request->session()->put('locale_chosen', true);

        App::setLocale($locale);

        cookie()->queue(cookie(self::COOKIE, $locale, self::COOKIE_MINUTES));
        cookie()->queue(cookie(self::CHOSEN_COOKIE, '1', self::COOKIE_MINUTES));
    }
}

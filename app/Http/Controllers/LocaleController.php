<?php

namespace App\Http\Controllers;

use App\Support\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(LocaleManager::SUPPORTED)],
        ]);

        LocaleManager::apply($request, $validated['locale']);

        if ($request->user()) {
            $request->user()->update(['locale' => $validated['locale']]);
        }

        $fromProfile = str_contains((string) url()->previous(), '/profile');

        return $fromProfile
            ? back()->with('success', __('ui.language_updated'))
            : back();
    }
}

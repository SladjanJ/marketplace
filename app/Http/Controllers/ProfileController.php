<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $ads = auth()->user()->ads()->with('coverImage')->latest()->get();

        return view('profile.show', compact('ads'));
    }
}

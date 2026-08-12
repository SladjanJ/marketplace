<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $ads = Ad::with('user')->latest()->get();

        return view('admin.dashboard', compact('ads'));
    }

    public function approve(Ad $ad)
    {
        $ad->update(['status' => 'approved']);

        return redirect()->route('admin.dashboard')->with('success', 'Ad approved.');
    }

    public function reject(Ad $ad)
    {
        $ad->update(['status' => 'rejected']);

        return redirect()->route('admin.dashboard')->with('success', 'Ad rejected.');
    }
}

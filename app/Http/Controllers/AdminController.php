<?php

namespace App\Http\Controllers;

use App\Models\Ad;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pendingAds = Ad::with('user')->where('status', 'pending')->latest()->get();
        $reviewedAds = Ad::with('user')->whereIn('status', ['approved', 'rejected'])->latest()->get();

        return view('admin.dashboard', compact('pendingAds', 'reviewedAds'));
    }

    public function approve(Ad $ad)
    {
        $ad->update(['status' => 'approved']);

        return redirect()->route('admin.dashboard')->with('success', __('ui.ad_approved'));
    }

    public function reject(Ad $ad)
    {
        $ad->update(['status' => 'rejected']);

        return redirect()->route('admin.dashboard')->with('success', __('ui.ad_rejected'));
    }
}

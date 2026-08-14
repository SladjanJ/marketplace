<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::with('images')
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('ads.index', compact('ads'));
    }

    public function create()
    {
        return view('ads.create', [
            'categories' => Ad::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'in:'.implode(',', Ad::CATEGORIES)],
            'location' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'images' => ['required', 'array', 'min:1', 'max:4'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $ad = $request->user()->ads()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'contact_info' => $validated['contact_email'].' · '.$validated['contact_phone'],
            'status' => 'pending',
        ]);

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('ads', 'public');
            $ad->images()->create(['path' => $path]);
        }

        return redirect()
            ->route('ads.index')
            ->with('success', __('ui.ad_submitted'));
    }
}

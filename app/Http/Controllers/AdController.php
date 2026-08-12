<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index()
    {
        $ads = Ad::with('images')->latest()->get();

        return view('ads.index', compact('ads'));
    }

    public function create()
    {
        return view('ads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'category' => ['required', 'string'],
            'location' => ['required', 'string'],
            'contact_info' => ['required', 'string'],
            'images' => ['required', 'array', 'min:1', 'max:4'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $ad = $request->user()->ads()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'contact_info' => $validated['contact_info'],
            'status' => 'pending',
        ]);

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('ads', 'public');
            $ad->images()->create(['path' => $path]);
        }

        return redirect()->route('ads.index')->with('success', 'Ad created successfully.');
    }
}

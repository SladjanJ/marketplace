<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdController extends Controller
{
    public function index(): View
    {
        $ads = Ad::with('images')
            ->where('status', 'approved')
            ->latest()
            ->get();

        $myAds = collect();

        if ($user = auth()->user()) {
            $myAds = $user->ads()
                ->with('images')
                ->where('status', '!=', 'approved')
                ->latest()
                ->get();
        }

        return view('ads.index', compact('ads', 'myAds'));
    }

    public function show(Ad $ad): View
    {
        $this->authorize('view', $ad);

        $ad->load(['images', 'user']);

        return view('ads.show', compact('ad'));
    }

    public function create(): View
    {
        return view('ads.create', [
            'categories' => Ad::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedAd($request, imagesRequired: true);

        $ad = $request->user()->ads()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'contact_info' => $this->contactInfo($validated),
            'status' => 'pending',
        ]);

        $this->storeImages($ad, $request->file('images', []));

        return redirect()
            ->route('ads.show', $ad)
            ->with('success', __('ui.ad_submitted'));
    }

    public function edit(Ad $ad): View
    {
        $this->authorize('update', $ad);

        $ad->load('images');

        return view('ads.edit', [
            'ad' => $ad,
            'categories' => Ad::CATEGORIES,
        ]);
    }

    public function update(Request $request, Ad $ad): RedirectResponse
    {
        $this->authorize('update', $ad);

        $ad->load('images');

        $validated = $this->validatedAd($request, imagesRequired: false);
        $removeIds = collect($validated['remove_images'] ?? [])->unique()->values();
        $newFiles = $request->file('images', []) ?? [];

        $remaining = $ad->images->whereNotIn('id', $removeIds)->count();
        $total = $remaining + count($newFiles);

        if ($total < 1 || $total > 4) {
            throw ValidationException::withMessages([
                'images' => __('ui.photos_count'),
            ]);
        }

        $ad->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'category' => $validated['category'],
            'location' => $validated['location'],
            'contact_info' => $this->contactInfo($validated),
            'status' => $ad->status === 'rejected' ? 'pending' : $ad->status,
        ]);

        $this->deleteImages($ad, $removeIds->all());
        $this->storeImages($ad, $newFiles);

        return redirect()
            ->route('ads.show', $ad)
            ->with('success', __('ui.ad_updated'));
    }

    public function updateStatus(Request $request, Ad $ad): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['approved', 'paused', 'sold'])],
        ]);

        $this->authorize('changeStatus', [$ad, $validated['status']]);

        $ad->update(['status' => $validated['status']]);

        return redirect()
            ->route('ads.show', $ad)
            ->with('success', __('ui.ad_status_'.$validated['status']));
    }

    public function destroy(Ad $ad): RedirectResponse
    {
        $this->authorize('delete', $ad);

        $ad->load('images');
        $this->deleteImages($ad, $ad->images->pluck('id')->all());
        $ad->delete();

        return redirect()
            ->route('ads.index')
            ->with('success', __('ui.ad_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedAd(Request $request, bool $imagesRequired): array
    {
        $imageRules = $imagesRequired
            ? ['required', 'array', 'min:1', 'max:4']
            : ['nullable', 'array', 'max:4'];

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['required', 'string', 'in:'.implode(',', Ad::CATEGORIES)],
            'location' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'images' => $imageRules,
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];

        if (! $imagesRequired) {
            $rules['remove_images'] = ['nullable', 'array'];
            $rules['remove_images.*'] = [
                'integer',
                Rule::exists('ad_images', 'id')->where('ad_id', $request->route('ad')?->id),
            ];
        }

        return $request->validate($rules);
    }

    /**
     * @param  array{contact_email: string, contact_phone: string}  $validated
     */
    private function contactInfo(array $validated): string
    {
        return $validated['contact_email'].' · '.$validated['contact_phone'];
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>|null  $images
     */
    private function storeImages(Ad $ad, ?array $images): void
    {
        foreach ($images ?? [] as $image) {
            $path = $image->store('ads', 'public');
            $ad->images()->create(['path' => $path]);
        }
    }

    /**
     * @param  array<int, int|string>  $imageIds
     */
    private function deleteImages(Ad $ad, array $imageIds): void
    {
        if ($imageIds === []) {
            return;
        }

        $images = $ad->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }
}

<a href="{{ route('ads.show', $ad) }}" class="card h-100 border-0 shadow-sm ad-list-card">
    @if($ad->images->isNotEmpty())
        <img src="{{ asset('storage/' . $ad->images->first()->path) }}" class="card-img-top ad-card-img" alt="{{ $ad->title }}">
    @endif
    <div class="card-body">
        @if($showStatus ?? false)
            <span class="badge {{ $ad->statusBadgeClass() }} mb-2">{{ $ad->translatedStatus() }}</span>
        @endif
        <h2 class="h5 card-title">{{ $ad->title }}</h2>
        <p class="card-text text-muted">{{ Str::limit($ad->description, 120) }}</p>
        <p class="fw-semibold mb-1">${{ number_format($ad->price, 2) }}</p>
        <p class="text-muted small mb-0">{{ $ad->translatedCategory() }} · {{ $ad->location }}</p>
    </div>
</a>

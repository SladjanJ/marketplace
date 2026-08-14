<a href="{{ route('ads.create') }}" class="card create-ad-card h-100">
    <div class="card-body d-flex flex-column align-items-center justify-content-center text-center py-5 px-4">
        <span class="create-ad-icon mb-3" aria-hidden="true">
            <i class="bi bi-plus-lg"></i>
        </span>
        <h2 class="h5 mb-2">{{ __('ui.create_ad') }}</h2>
        <p class="text-muted mb-0 small">{{ __('ui.create_ad_hint') }}</p>
    </div>
</a>

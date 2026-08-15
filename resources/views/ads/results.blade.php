<div class="{{ $ads->isEmpty() ? 'col-12 col-lg-6' : 'col-12 col-sm-6 col-lg-4' }}">
    @include('partials.create-ad-card')
</div>

@forelse($ads as $ad)
    <div class="col-12 col-sm-6 col-lg-4">
        @include('partials.ad-card', ['ad' => $ad, 'showStatus' => false])
    </div>
@empty
    @if($hasFilters)
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center text-muted">
                    {{ __('ui.no_filter_results') }}
                </div>
            </div>
        </div>
    @endif
@endforelse

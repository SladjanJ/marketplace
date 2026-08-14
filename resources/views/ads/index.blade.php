@extends('layout')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('ui.latest_ads') }}</h1>
        <p class="text-muted mb-0">{{ __('ui.browse_ads') }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="{{ $ads->isEmpty() ? 'col-12 col-lg-6' : 'col-12 col-sm-6 col-lg-4' }}">
        @include('partials.create-ad-card')
    </div>

    @foreach($ads as $ad)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                @if($ad->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $ad->images->first()->path) }}" class="card-img-top ad-card-img" alt="{{ $ad->title }}">
                @endif
                <div class="card-body">
                    <h2 class="h5 card-title">{{ $ad->title }}</h2>
                    <p class="card-text text-muted">{{ Str::limit($ad->description, 120) }}</p>
                    <p class="fw-semibold mb-1">${{ number_format($ad->price, 2) }}</p>
                    <p class="text-muted small mb-0">{{ $ad->translatedCategory() }} · {{ $ad->location }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

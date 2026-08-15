@extends('layout')

@section('content')
@auth
    @if($myAds->isNotEmpty())
        <div class="mb-5">
            <h2 class="h4 mb-1">{{ __('ui.my_ads') }}</h2>
            <p class="text-muted mb-3">{{ __('ui.my_ads_intro') }}</p>
            <div class="row g-4">
                @foreach($myAds as $ad)
                    <div class="col-12 col-sm-6 col-lg-4">
                        @include('partials.ad-card', ['ad' => $ad, 'showStatus' => true])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endauth

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
            @include('partials.ad-card', ['ad' => $ad, 'showStatus' => false])
        </div>
    @endforeach
</div>
@endsection

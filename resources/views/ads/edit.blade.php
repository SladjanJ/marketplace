@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">{{ __('ui.edit_ad_title') }}</h1>
                <p class="text-muted mb-0">{{ __('ui.edit_ad_subtitle') }}</p>
            </div>
            <a href="{{ route('ads.show', $ad) }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.back') }}</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                @include('ads.form', ['ad' => $ad, 'categories' => $categories])
            </div>
        </div>
    </div>
</div>
@endsection

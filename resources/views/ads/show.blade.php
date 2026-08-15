@extends('layout')

@section('content')
<a href="{{ route('ads.index') }}" class="d-inline-flex align-items-center gap-1 text-muted text-decoration-none mb-3">
    <i class="bi bi-arrow-left" aria-hidden="true"></i>
    {{ __('ui.back_to_ads') }}
</a>

@can('update', $ad)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <span class="badge {{ $ad->statusBadgeClass() }} mb-2">{{ $ad->translatedStatus() }}</span>
            <p class="mb-0 text-muted">{{ __('ui.ad_status_help_'.$ad->status) }}</p>
        </div>
    </div>
@endcan

<div class="row g-4">
    <div class="col-lg-7">
        @if($ad->images->isNotEmpty())
            <div id="adGallery" class="carousel slide card border-0 shadow-sm overflow-hidden" data-bs-ride="false">
                <div class="carousel-inner">
                    @foreach($ad->images as $index => $image)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <button
                                type="button"
                                class="ad-hero-trigger"
                                data-bs-toggle="modal"
                                data-bs-target="#adImageLightbox"
                                data-image="{{ asset('storage/'.$image->path) }}"
                                aria-label="{{ __('ui.view_full_image') }}"
                            >
                                <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100 ad-hero-img" alt="{{ $ad->title }}">
                            </button>
                        </div>
                    @endforeach
                </div>
                @if($ad->images->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#adGallery" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{ __('ui.previous') }}</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#adGallery" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">{{ __('ui.next') }}</span>
                    </button>
                @endif
            </div>
            <p class="small text-muted mt-2 mb-0">{{ __('ui.click_image_to_enlarge') }}</p>
        @endif
    </div>

    <div class="col-lg-5">
        <h1 class="h3 mb-1">{{ $ad->title }}</h1>
        <p class="text-muted mb-3">{{ $ad->translatedCategory() }} · {{ $ad->location }}</p>

        <p class="fs-4 fw-semibold mb-3">${{ number_format($ad->price, 2) }}</p>
        <p class="mb-4">{{ $ad->description }}</p>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 mb-3">{{ __('ui.contact_seller') }}</h2>
                @auth
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('ui.contact_email') }}</dt>
                        <dd class="col-sm-8">{{ $ad->contactEmail() }}</dd>
                        <dt class="col-sm-4">{{ __('ui.contact_phone') }}</dt>
                        <dd class="col-sm-8 mb-0">{{ $ad->contactPhone() }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-3">{{ __('ui.login_to_contact') }}</p>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">{{ __('ui.login') }}</a>
                @endauth
            </div>
        </div>

        @can('update', $ad)
            <div class="d-flex flex-wrap gap-2">
                @if($ad->ownerCanTransitionTo('paused'))
                    <form method="POST" action="{{ route('ads.status', $ad) }}" onsubmit="return confirm(@json(__('ui.confirm_pause_ad')))">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="paused">
                        <button type="submit" class="btn btn-secondary">{{ __('ui.pause_ad') }}</button>
                    </form>
                @endif
                @if($ad->ownerCanTransitionTo('approved'))
                    <form method="POST" action="{{ route('ads.status', $ad) }}" onsubmit="return confirm(@json(__('ui.confirm_resume_ad')))">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn btn-success">{{ __('ui.resume_ad') }}</button>
                    </form>
                @endif
                @if($ad->ownerCanTransitionTo('sold'))
                    <form method="POST" action="{{ route('ads.status', $ad) }}" onsubmit="return confirm(@json(__('ui.confirm_sold_ad')))">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="sold">
                        <button type="submit" class="btn btn-outline-dark">{{ __('ui.mark_sold') }}</button>
                    </form>
                @endif
                <a href="{{ route('ads.edit', $ad) }}" class="btn btn-outline-primary">{{ __('ui.edit_ad') }}</a>
                @can('delete', $ad)
                    <form method="POST" action="{{ route('ads.destroy', $ad) }}" onsubmit="return confirm(@json(__('ui.confirm_delete_ad')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">{{ __('ui.delete_ad') }}</button>
                    </form>
                @endcan
            </div>
        @endcan
    </div>
</div>

@if($ad->images->isNotEmpty())
    <div class="modal fade" id="adImageLightbox" tabindex="-1" aria-hidden="true" aria-labelledby="adImageLightboxLabel">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"></button>
                </div>
                <img id="adLightboxImage" src="{{ asset('storage/'.$ad->images->first()->path) }}" alt="{{ $ad->title }}" class="ad-lightbox-img mx-auto d-block">
                <p id="adImageLightboxLabel" class="visually-hidden">{{ __('ui.view_full_image') }}</p>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if($ad->images->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('adImageLightbox');
        const image = document.getElementById('adLightboxImage');
        if (!modal || !image) return;

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const src = trigger?.getAttribute('data-image');
            if (src) {
                image.src = src;
            }
        });
    });
</script>
@endif
@endpush

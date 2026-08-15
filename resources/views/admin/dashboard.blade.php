@extends('layout')

@section('content')
<h1 class="h3 mb-4">{{ __('ui.admin_dashboard') }}</h1>

<h2 class="h5 mb-3">{{ __('ui.pending_ads') }}</h2>
<div class="table-responsive mb-5">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>{{ __('ui.title') }}</th>
                <th>{{ __('ui.user') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendingAds as $ad)
                <tr>
                    <td><a href="{{ route('ads.show', $ad) }}">{{ $ad->title }}</a></td>
                    <td>{{ $ad->user->name }}</td>
                    <td><span class="badge {{ $ad->statusBadgeClass() }}">{{ $ad->translatedStatus() }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.ads.approve', $ad) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-success btn-sm">{{ __('ui.approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.ads.reject', $ad) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-danger btn-sm">{{ __('ui.reject') }}</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-muted">{{ __('ui.no_pending_ads') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<h2 class="h5 mb-3">{{ __('ui.reviewed_ads') }}</h2>
<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>{{ __('ui.title') }}</th>
                <th>{{ __('ui.user') }}</th>
                <th>{{ __('ui.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviewedAds as $ad)
                <tr>
                    <td><a href="{{ route('ads.show', $ad) }}">{{ $ad->title }}</a></td>
                    <td>{{ $ad->user->name }}</td>
                    <td><span class="badge {{ $ad->statusBadgeClass() }}">{{ $ad->translatedStatus() }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-muted">{{ __('ui.no_reviewed_ads') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

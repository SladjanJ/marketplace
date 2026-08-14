@extends('layout')

@section('content')
<h1>{{ __('ui.admin_dashboard') }}</h1>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ __('ui.title') }}</th>
                <th>{{ __('ui.user') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ads as $ad)
                <tr>
                    <td>{{ $ad->title }}</td>
                    <td>{{ $ad->user->name }}</td>
                    <td>{{ __('status.'.$ad->status) }}</td>
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
            @endforeach
        </tbody>
    </table>
</div>
@endsection

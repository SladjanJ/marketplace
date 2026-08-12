@extends('layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Latest ads</h1>
    @auth
        @if(auth()->user()->hasVerifiedEmail())
            <a class="btn btn-primary" href="{{ route('ads.create') }}">Create ad</a>
        @endif
    @endauth
</div>

<div class="row g-4">
    @foreach($ads as $ad)
        <div class="col-md-6">
            <div class="card h-100">
                @if($ad->images->isNotEmpty())
                    <img src="{{ asset('storage/' . $ad->images->first()->path) }}" class="card-img-top" alt="{{ $ad->title }}" style="height: 220px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h5 class="card-title">{{ $ad->title }}</h5>
                    <p class="card-text">{{ Str::limit($ad->description, 120) }}</p>
                    <p class="fw-bold">${{ number_format($ad->price, 2) }}</p>
                    <p class="text-muted">{{ $ad->category }} · {{ $ad->location }}</p>
                    <span class="badge bg-info text-dark">{{ ucfirst($ad->status) }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

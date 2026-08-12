@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-3">Verify your email</h1>
        <p class="mb-4">
            Thanks for registering. We sent a verification link to
            <strong>{{ auth()->user()->email }}</strong>.
            Click the link in that email to activate your account. After verification you will land on the marketplace home page as a logged-in user.
        </p>

        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-primary">Resend verification email</button>
        </form>

        <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary btn-sm me-2">Back to home</a>

        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
        </form>
    </div>
</div>
@endsection

@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Register</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                <div class="form-text">Minimum 8 characters.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="mt-3 mb-0">
            Already have an account?
            <a href="{{ route('login') }}">Login</a>
        </p>
    </div>
</div>
@endsection

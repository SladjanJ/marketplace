@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Login</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p class="mt-3 mb-0">
            Need an account?
            <a href="{{ route('register') }}">Register</a>
        </p>
    </div>
</div>
@endsection

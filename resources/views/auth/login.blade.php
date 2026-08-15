@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">{{ __('ui.login') }}</h1>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">{{ __('ui.password') }}</label>
                @include('partials.password-input', ['autocomplete' => 'current-password'])
                <div class="mt-2">
                    <a href="{{ route('password.request') }}">{{ __('ui.forgot_password') }}</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('ui.login') }}</button>
        </form>

        <p class="mt-3 mb-0">
            {{ __('ui.need_account') }}
            <a href="{{ route('register') }}">{{ __('ui.register') }}</a>
        </p>
    </div>
</div>
@endsection

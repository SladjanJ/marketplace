@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">{{ __('ui.forgot_password_title') }}</h1>
        <p class="mb-4 text-muted">
            {{ __('ui.forgot_password_intro') }}
        </p>

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">{{ __('ui.send_reset_link') }}</button>
        </form>

        <p class="mt-3 mb-0">
            <a href="{{ route('login') }}">{{ __('ui.back_to_login') }}</a>
        </p>
    </div>
</div>
@endsection

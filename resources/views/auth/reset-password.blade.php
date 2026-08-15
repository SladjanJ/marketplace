@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">{{ __('ui.reset_password') }}</h1>
        <p class="mb-4 text-muted">
            {{ __('ui.reset_password_intro') }}
        </p>

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label class="form-label" for="email">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">{{ __('ui.new_password') }}</label>
                @include('partials.password-input', ['minlength' => 8, 'autocomplete' => 'new-password'])
                <div class="form-text">{{ __('ui.password_min') }}</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">{{ __('ui.confirm_new_password') }}</label>
                @include('partials.password-input', [
                    'id' => 'password_confirmation',
                    'name' => 'password_confirmation',
                    'minlength' => 8,
                    'autocomplete' => 'new-password',
                ])
            </div>
            <button type="submit" class="btn btn-primary">{{ __('ui.save_new_password') }}</button>
        </form>

        <p class="mt-3 mb-0">
            <a href="{{ route('login') }}">{{ __('ui.back_to_login') }}</a>
        </p>
    </div>
</div>
@endsection

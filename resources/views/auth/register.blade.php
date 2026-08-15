@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">{{ __('ui.register') }}</h1>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="name">{{ __('ui.name') }}</label>
                <input id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">{{ __('ui.email') }}</label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">{{ __('ui.password') }}</label>
                @include('partials.password-input', ['minlength' => 8, 'autocomplete' => 'new-password'])
                @unless($errors->has('password'))
                    <div class="form-text">{{ __('ui.password_min') }}</div>
                @endunless
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">{{ __('ui.confirm_password') }}</label>
                @include('partials.password-input', [
                    'id' => 'password_confirmation',
                    'name' => 'password_confirmation',
                    'minlength' => 8,
                    'autocomplete' => 'new-password',
                ])
            </div>
            <button type="submit" class="btn btn-primary">{{ __('ui.register') }}</button>
        </form>

        <p class="mt-3 mb-0">
            {{ __('ui.already_have_account') }}
            <a href="{{ route('login') }}">{{ __('ui.login') }}</a>
        </p>
    </div>
</div>
@endsection

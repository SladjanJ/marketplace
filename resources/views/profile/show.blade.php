@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 mb-1">{{ __('ui.profile_title') }}</h1>
        <p class="text-muted mb-4">{{ __('ui.profile_intro') }}</p>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('ui.name') }}</dt>
                    <dd class="col-sm-8">{{ auth()->user()->name }}</dd>
                    <dt class="col-sm-4">{{ __('ui.email') }}</dt>
                    <dd class="col-sm-8 mb-0">{{ auth()->user()->email }}</dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">{{ __('ui.settings') }}</h2>
                <form method="POST" action="{{ route('locale.update') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="locale">{{ __('ui.language') }}</label>
                        <select id="locale" name="locale" class="form-select @error('locale') is-invalid @enderror">
                            <option value="en" @selected(app()->getLocale() === 'en')>{{ __('ui.english') }}</option>
                            <option value="sr" @selected(app()->getLocale() === 'sr')>{{ __('ui.serbian') }}</option>
                        </select>
                        @error('locale')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">{{ __('ui.language_help') }}</div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ __('ui.save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

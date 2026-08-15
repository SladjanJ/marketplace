@php
    $id = $id ?? 'password';
    $name = $name ?? 'password';
    $required = $required ?? true;
    $minlength = $minlength ?? null;
    $autocomplete = $autocomplete ?? 'current-password';
@endphp

<div class="input-group has-validation">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="password"
        class="form-control @error($name) is-invalid @enderror"
        @required($required)
        @if($minlength) minlength="{{ $minlength }}" @endif
        autocomplete="{{ $autocomplete }}"
    >
    <button
        type="button"
        class="btn btn-outline-secondary password-toggle"
        data-target="{{ $id }}"
        data-show-label="{{ __('ui.show_password') }}"
        data-hide-label="{{ __('ui.hide_password') }}"
        aria-label="{{ __('ui.show_password') }}"
        aria-pressed="false"
    >
        <i class="bi bi-eye" aria-hidden="true"></i>
    </button>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

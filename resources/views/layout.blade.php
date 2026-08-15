<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Marketplace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f6f7f9; }
        .navbar-brand { font-weight: 600; letter-spacing: .01em; }
        .create-ad-card {
            min-height: 100%;
            border: 2px dashed #cfd6dd;
            background: #fff;
            color: inherit;
            text-decoration: none;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .create-ad-card:hover,
        .create-ad-card:focus {
            border-color: #0d6efd;
            box-shadow: 0 0.5rem 1.25rem rgba(13, 110, 253, .08);
            transform: translateY(-1px);
            color: inherit;
        }
        .create-ad-icon {
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 999px;
            background: #eef4ff;
            color: #0d6efd;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .ad-card-img {
            height: 200px;
            object-fit: cover;
        }
        .ad-list-card {
            color: inherit;
            text-decoration: none;
            transition: box-shadow .15s ease, transform .15s ease;
        }
        .ad-list-card:hover,
        .ad-list-card:focus {
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, .12);
            transform: translateY(-1px);
            color: inherit;
        }
        .ad-hero-img {
            height: 420px;
            object-fit: cover;
            background: #e9ecef;
        }
        .ad-hero-trigger {
            display: block;
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            cursor: zoom-in;
        }
        .ad-lightbox-img {
            max-width: 100%;
            max-height: 90vh;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: .5rem;
        }
        #adImageLightbox .modal-content {
            box-shadow: none;
        }
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .password-toggle {
            min-width: 2.75rem;
        }
        .location-suggest-list {
            position: absolute;
            top: calc(100% + .25rem);
            left: 0;
            right: 0;
            z-index: 20;
            max-height: 14rem;
            overflow: auto;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            box-shadow: 0 .5rem 1.25rem rgba(15, 23, 42, .12);
        }
        .location-suggest-list li {
            padding: .5rem .75rem;
            cursor: pointer;
        }
        .location-suggest-list li:hover,
        .location-suggest-list li.active {
            background: #eef4ff;
        }
        @media (max-width: 575.98px) {
            .ad-card-img { height: 180px; }
            .ad-hero-img { height: 240px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('ads.index') }}">Marketplace</a>

        <div class="d-flex align-items-center gap-2 order-lg-last">
            <a href="{{ route('ads.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                <i class="bi bi-plus-lg"></i>
                <span class="d-none d-sm-inline">{{ __('ui.new_ad') }}</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="{{ __('ui.toggle_navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="mainNav">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-2 ms-lg-auto me-lg-3 mt-3 mt-lg-0">
                @auth
                    @admin
                        <a class="btn btn-outline-light btn-sm" href="{{ route('admin.dashboard') }}">{{ __('ui.admin') }}</a>
                    @endadmin
                    <a class="btn btn-outline-light btn-sm" href="{{ route('profile.show') }}">{{ __('ui.profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">{{ __('ui.logout') }}</button>
                    </form>
                @else
                    <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">{{ __('ui.login') }}</a>
                    <a class="btn btn-light btn-sm" href="{{ route('register') }}">{{ __('ui.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @yield('content')
</div>

@if($needsLanguageChoice ?? false)
    <noscript>
        <div class="position-fixed bottom-0 start-0 end-0 p-3 bg-white border-top shadow-sm" style="z-index: 1080;">
            <div class="container">
                <p class="mb-2 fw-semibold">Choose your language / Izaberite jezik</p>
                <form method="POST" action="{{ route('locale.update') }}" class="d-flex gap-2">
                    @csrf
                    <button type="submit" name="locale" value="sr" class="btn btn-primary">Srpski</button>
                    <button type="submit" name="locale" value="en" class="btn btn-outline-primary">English</button>
                </form>
            </div>
        </div>
    </noscript>

    <form id="locale-dismiss-form" method="POST" action="{{ route('locale.update') }}" class="d-none">
        @csrf
        <input type="hidden" name="locale" value="en">
    </form>

    <div class="modal fade" id="languageModal" tabindex="-1" aria-labelledby="languageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="languageModalLabel">Choose your language / Izaberite jezik</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close / Zatvori"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-4">The site will stay in the language you pick. You can change it later in your profile settings.<br>Sajt ostaje na jeziku koji izaberete. Kasnije ga možete promeniti u podešavanjima profila.</p>
                    <form method="POST" action="{{ route('locale.update') }}" class="d-grid gap-2" onsubmit="window.__localeChosen = true">
                        @csrf
                        <button type="submit" name="locale" value="sr" class="btn btn-primary btn-lg">Srpski</button>
                        <button type="submit" name="locale" value="en" class="btn btn-outline-primary btn-lg">English</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.password-toggle');
        if (!button) return;

        const input = document.getElementById(button.getAttribute('data-target'));
        const icon = button.querySelector('i');
        if (!input) return;

        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', show ? 'true' : 'false');
        button.setAttribute('aria-label', show ? button.dataset.hideLabel : button.dataset.showLabel);
        if (icon) {
            icon.classList.toggle('bi-eye', !show);
            icon.classList.toggle('bi-eye-slash', show);
        }
    });
</script>
@if($needsLanguageChoice ?? false)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('languageModal');
        if (!el || typeof bootstrap === 'undefined') return;

        const modal = new bootstrap.Modal(el);
        modal.show();

        el.addEventListener('hidden.bs.modal', function () {
            if (window.__localeChosen) return;
            document.getElementById('locale-dismiss-form')?.submit();
        });
    });
</script>
@endif
@stack('scripts')
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marketplace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('ads.index') }}">Marketplace</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            @auth
                @unless(auth()->user()->hasVerifiedEmail())
                    <a class="btn btn-warning btn-sm" href="{{ route('verification.notice') }}">Verify email</a>
                @endunless
                @admin
                    <a class="btn btn-outline-light btn-sm" href="{{ route('admin.dashboard') }}">Admin</a>
                @endadmin
                <span class="text-white">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            @else
                <a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Login</a>
                <a class="btn btn-light btn-sm" href="{{ route('register') }}">Register</a>
            @endauth
        </div>
    </div>
</nav>
<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @yield('content')
</div>
</body>
</html>

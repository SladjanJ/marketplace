@extends('layout')

@section('content')
@auth
    @if($myAds->isNotEmpty())
        <div class="mb-5">
            <h2 class="h4 mb-1">{{ __('ui.my_ads') }}</h2>
            <p class="text-muted mb-3">{{ __('ui.my_ads_intro') }}</p>
            <div class="row g-4">
                @foreach($myAds as $ad)
                    <div class="col-12 col-sm-6 col-lg-4">
                        @include('partials.ad-card', ['ad' => $ad, 'showStatus' => true])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endauth

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">{{ __('ui.latest_ads') }}</h1>
        <p class="text-muted mb-0">{{ __('ui.browse_ads') }}</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form id="ads-filter-form" method="GET" action="{{ route('ads.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label" for="q">{{ __('ui.search') }}</label>
                <input id="q" name="q" type="search" class="form-control @error('q') is-invalid @enderror" value="{{ old('q', $filters['q']) }}" placeholder="{{ __('ui.search_placeholder') }}" maxlength="100">
                @error('q')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label" for="category">{{ __('ui.category') }}</label>
                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                    <option value="">{{ __('ui.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $filters['category']) === $category)>{{ __('categories.'.$category) }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label" for="location">{{ __('ui.location') }}</label>
                <div class="location-suggest position-relative">
                    <input
                        id="location"
                        name="location"
                        class="form-control @error('location') is-invalid @enderror"
                        value="{{ old('location', $filters['location']) }}"
                        placeholder="{{ __('ui.location_placeholder') }}"
                        maxlength="100"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        role="combobox"
                        aria-expanded="false"
                        aria-controls="location-suggestions"
                        aria-autocomplete="list"
                    >
                    <ul id="location-suggestions" class="location-suggest-list list-unstyled mb-0" hidden role="listbox" aria-label="{{ __('ui.location_suggestions') }}"></ul>
                </div>
                @error('location')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="min_price">{{ __('ui.min_price') }}</label>
                <input id="min_price" name="min_price" type="number" step="0.01" min="0" class="form-control @error('min_price') is-invalid @enderror" value="{{ old('min_price', $filters['min_price']) }}">
                @error('min_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="max_price">{{ __('ui.max_price') }}</label>
                <input id="max_price" name="max_price" type="number" step="0.01" min="0" class="form-control @error('max_price') is-invalid @enderror" value="{{ old('max_price', $filters['max_price']) }}">
                @error('max_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label" for="sort">{{ __('ui.sort') }}</label>
                <select id="sort" name="sort" class="form-select @error('sort') is-invalid @enderror">
                    <option value="newest" @selected(old('sort', $filters['sort']) === 'newest')>{{ __('ui.sort_newest') }}</option>
                    <option value="price_asc" @selected(old('sort', $filters['sort']) === 'price_asc')>{{ __('ui.sort_price_asc') }}</option>
                    <option value="price_desc" @selected(old('sort', $filters['sort']) === 'price_desc')>{{ __('ui.sort_price_desc') }}</option>
                </select>
                @error('sort')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 col-sm-6 col-lg-9 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">{{ __('ui.apply_filters') }}</button>
                <a
                    id="clear-filters"
                    href="{{ route('ads.index') }}"
                    class="btn btn-outline-secondary{{ $hasFilters ? '' : ' d-none' }}"
                >{{ __('ui.clear_filters') }}</a>
            </div>
        </form>
    </div>
</div>

<div id="ads-results" class="row g-4" data-has-filters="{{ $hasFilters ? '1' : '0' }}">
    @include('ads.results')
</div>

<script type="application/json" id="location-cities">@json($locations)</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('location');
        const list = document.getElementById('location-suggestions');
        const form = document.getElementById('ads-filter-form');
        const results = document.getElementById('ads-results');
        const clearButton = document.getElementById('clear-filters');
        const citiesNode = document.getElementById('location-cities');
        if (!input || !list || !form || !results || !citiesNode) return;

        const cities = JSON.parse(citiesNode.textContent || '[]');
        const minLength = 3;
        let debounceId = null;
        let activeIndex = -1;
        let requestId = 0;

        const normalize = (value) => value
            .toLocaleLowerCase()
            .replaceAll('č', 'c')
            .replaceAll('ć', 'c')
            .replaceAll('š', 's')
            .replaceAll('ž', 'z')
            .replaceAll('đ', 'dj');

        const suggestions = (query) => {
            const needle = normalize(query.trim());
            if (needle.length < minLength) return [];

            return cities.filter((city) => normalize(city).startsWith(needle)).slice(0, 8);
        };

        const closeList = () => {
            list.hidden = true;
            list.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        };

        const highlight = () => {
            Array.from(list.children).forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
        };

        const renderSuggestions = (items) => {
            if (items.length === 0) {
                closeList();
                return;
            }

            list.replaceChildren();
            items.forEach((city, index) => {
                const item = document.createElement('li');
                item.setAttribute('role', 'option');
                item.dataset.index = String(index);
                item.textContent = city;
                list.appendChild(item);
            });
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            activeIndex = -1;
        };

        const chooseCity = (city) => {
            input.value = city;
            closeList();
            applyLocationFilter();
        };

        const visibleUrl = () => {
            const params = new URLSearchParams(new FormData(form));
            ['q', 'category', 'location', 'min_price', 'max_price', 'sort'].forEach((key) => {
                const value = (params.get(key) || '').trim();
                if (value === '' || (key === 'sort' && value === 'newest')) {
                    params.delete(key);
                } else {
                    params.set(key, value);
                }
            });
            params.delete('partial');
            const query = params.toString();
            return query ? `${form.action}?${query}` : form.action;
        };

        const applyLocationFilter = async () => {
            const term = input.value.trim();
            if (term.length > 0 && term.length < minLength) {
                return;
            }

            const params = new URLSearchParams(new FormData(form));
            params.set('partial', '1');
            if (term === '') {
                params.delete('location');
            }

            const current = ++requestId;
            history.replaceState(null, '', visibleUrl());

            try {
                const response = await fetch(`${form.action}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok || current !== requestId) return;

                results.innerHTML = await response.text();
                const hasFilters = Boolean(new URL(visibleUrl(), window.location.origin).search);
                results.dataset.hasFilters = hasFilters ? '1' : '0';
                clearButton?.classList.toggle('d-none', !hasFilters);
            } catch (error) {
                // Keep the current list if the live request fails.
            }
        };

        input.addEventListener('input', () => {
            renderSuggestions(suggestions(input.value));
            clearTimeout(debounceId);
            debounceId = setTimeout(applyLocationFilter, 300);
        });

        list.addEventListener('mousedown', (event) => {
            const item = event.target.closest('li');
            if (!item) return;
            event.preventDefault();
            chooseCity(item.textContent);
        });

        input.addEventListener('keydown', (event) => {
            const items = Array.from(list.children);
            if (event.key === 'Escape') {
                closeList();
                return;
            }
            if (items.length === 0) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                highlight();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                highlight();
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                chooseCity(items[activeIndex].textContent);
            }
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.location-suggest')) {
                closeList();
            }
        });
    });
</script>
@endpush

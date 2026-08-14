@extends('layout')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h1 class="h3 mb-1">{{ __('ui.create_ad_title') }}</h1>
                <p class="text-muted mb-0">{{ __('ui.create_ad_subtitle') }}</p>
            </div>
            <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('ui.back') }}</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="title">{{ __('ui.title') }}</label>
                            <input id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="price">{{ __('ui.price') }}</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">{{ __('ui.description') }}</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="category">{{ __('ui.category') }}</label>
                            <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>{{ __('ui.choose_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ __('categories.'.$category) }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="location">{{ __('ui.location') }}</label>
                            <input id="location" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contact_email">{{ __('ui.contact_email') }}</label>
                            <input id="contact_email" name="contact_email" type="email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', auth()->user()->email) }}" required>
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="contact_phone">{{ __('ui.contact_phone') }}</label>
                            <input id="contact_phone" name="contact_phone" type="tel" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone') }}" required>
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="images">{{ __('ui.photos') }}</label>
                            <input id="images" name="images[]" type="file" class="form-control @error('images') is-invalid @enderror {{ $errors->has('images.*') ? 'is-invalid' : '' }}" accept="image/jpeg,image/png,image/webp" multiple required>
                            @error('images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if ($errors->has('images.*'))
                                <div class="invalid-feedback d-block">{{ $errors->first('images.*') }}</div>
                            @endif
                            <div class="form-text">{{ __('ui.photos_help') }}</div>
                            <div id="image-preview" class="row g-2 mt-2 image-preview"></div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">{{ __('ui.submit_review') }}</button>
                        <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary">{{ __('ui.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('images');
        const preview = document.getElementById('image-preview');
        if (!input || !preview) return;

        input.addEventListener('change', function () {
            const files = Array.from(input.files || []).slice(0, 4);

            if ((input.files || []).length > 4) {
                const transfer = new DataTransfer();
                files.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
            }

            preview.innerHTML = '';
            files.forEach((file) => {
                if (!file.type.startsWith('image/')) return;

                const col = document.createElement('div');
                col.className = 'col-6 col-md-3';
                col.innerHTML = '<div class="ratio ratio-1x1 rounded overflow-hidden border bg-light"><img alt=""></div>';
                col.querySelector('img').src = URL.createObjectURL(file);
                preview.appendChild(col);
            });
        });
    })();
</script>
@endpush

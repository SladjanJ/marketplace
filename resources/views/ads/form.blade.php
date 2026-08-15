@php
    $isEdit = $ad instanceof \App\Models\Ad;
    $action = $isEdit ? route('ads.update', $ad) : route('ads.store');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="title">{{ __('ui.title') }}</label>
            <input id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $ad?->title) }}" required maxlength="255">
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label" for="price">{{ __('ui.price') }}</label>
            <input id="price" name="price" type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $ad?->price) }}" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="description">{{ __('ui.description') }}</label>
            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description', $ad?->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="category">{{ __('ui.category') }}</label>
            <select id="category" name="category" class="form-select @error('category') is-invalid @enderror" required>
                <option value="" disabled {{ old('category', $ad?->category) ? '' : 'selected' }}>{{ __('ui.choose_category') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(old('category', $ad?->category) === $category)>{{ __('categories.'.$category) }}</option>
                @endforeach
            </select>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="location">{{ __('ui.location') }}</label>
            <input id="location" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $ad?->location) }}" required>
            @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="contact_email">{{ __('ui.contact_email') }}</label>
            <input id="contact_email" name="contact_email" type="email" class="form-control @error('contact_email') is-invalid @enderror" value="{{ old('contact_email', $isEdit ? $ad->contactEmail() : auth()->user()->email) }}" required>
            @error('contact_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="contact_phone">{{ __('ui.contact_phone') }}</label>
            <input id="contact_phone" name="contact_phone" type="tel" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone', $isEdit ? $ad->contactPhone() : '') }}" required>
            @error('contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label">{{ __('ui.photos') }}</label>
            @if($isEdit && $ad->images->isNotEmpty())
                <div class="row g-2 mb-3">
                    @foreach($ad->images as $image)
                        <div class="col-6 col-md-3">
                            <div class="position-relative border rounded overflow-hidden bg-light">
                                <img src="{{ asset('storage/'.$image->path) }}" alt="" class="w-100 ad-card-img">
                                <div class="form-check position-absolute bottom-0 start-0 m-2 bg-white rounded px-2 py-1">
                                    <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove-image-{{ $image->id }}">
                                    <label class="form-check-label small" for="remove-image-{{ $image->id }}">{{ __('ui.remove_photo') }}</label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <input id="images" name="images[]" type="file" class="form-control @error('images') is-invalid @enderror {{ $errors->has('images.*') ? 'is-invalid' : '' }}" accept="image/jpeg,image/png,image/webp" multiple @required(! $isEdit)>
            @error('images')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @if ($errors->has('images.*'))
                <div class="invalid-feedback d-block">{{ $errors->first('images.*') }}</div>
            @endif
            <div class="form-text">{{ $isEdit ? __('ui.photos_help_edit') : __('ui.photos_help') }}</div>
            <div id="image-preview" class="row g-2 mt-2 image-preview"></div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? __('ui.save_changes') : __('ui.submit_review') }}</button>
        <a href="{{ $isEdit ? route('ads.show', $ad) : route('ads.index') }}" class="btn btn-outline-secondary">{{ __('ui.cancel') }}</a>
    </div>
</form>

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

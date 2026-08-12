@extends('layout')

@section('content')
<h1>Create ad</h1>
<form method="POST" action="{{ route('ads.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4" required></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Price</label>
        <input name="price" type="number" step="0.01" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Category</label>
        <input name="category" class="form-control" value="Prodaja" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Location</label>
        <input name="location" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Contact info</label>
        <input name="contact_info" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Images (1-4)</label>
        <input name="images[]" type="file" class="form-control" multiple required>
    </div>
    <button class="btn btn-primary">Publish</button>
</form>
@endsection

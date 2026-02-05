@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('Edit Brand') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="year" class="form-label">{{ __('Year') }}</label>
                            <input type="number" name="year" id="year" class="form-control @error('year') is-invalid @enderror" value="{{ old('year', $category->year) }}" min="1900" max="2100" placeholder="{{ __('e.g. 2024') }}">
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="count" class="form-label">{{ __('Count') }}</label>
                            <input type="number" name="count" id="count" class="form-control @error('count') is-invalid @enderror" value="{{ old('count', $category->count ?? 0) }}" min="0">
                            @error('count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">{{ __('Image') }}</label>
                            @if($category->image)
                                <div class="mb-2" id="currentImageContainer">
                                    @if(str_starts_with($category->image, 'http'))
                                        <img src="{{ $category->image }}" alt="{{ $category->name }}" class="rounded" style="max-height: 80px; width: auto;">
                                    @else
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="rounded" style="max-height: 80px; width: auto;">
                                    @endif
                                    <div class="form-check mt-2">
                                        <input type="hidden" name="remove_image" value="0">
                                        <input type="checkbox" name="remove_image" id="remove_image" value="1" class="form-check-input" {{ old('remove_image') ? 'checked' : '' }} onchange="toggleImageRemoval(this)">
                                        <label for="remove_image" class="form-check-label">{{ __('Remove image') }}</label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewImage(event)">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('Max 2MB. Formats: JPEG, PNG, GIF, WebP. Leave empty to keep current.') }}</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="preview" src="" alt="Preview" class="rounded" style="max-height: 150px; width: auto;">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    const currentImageContainer = document.getElementById('currentImageContainer');
    const removeCheckbox = document.getElementById('remove_image');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
            if (currentImageContainer) {
                currentImageContainer.style.opacity = '0.5';
            }
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
        }
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
        if (currentImageContainer) {
            currentImageContainer.style.opacity = '1';
        }
    }
}

function toggleImageRemoval(checkbox) {
    const currentImageContainer = document.getElementById('currentImageContainer');
    const fileInput = document.getElementById('image');
    
    if (checkbox.checked) {
        if (currentImageContainer) {
            currentImageContainer.style.opacity = '0.5';
        }
        fileInput.disabled = true;
    } else {
        if (currentImageContainer) {
            currentImageContainer.style.opacity = '1';
        }
        fileInput.disabled = false;
    }
}
</script>
@endpush

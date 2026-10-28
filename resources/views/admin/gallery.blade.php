@extends('layouts.admin')
@section('title', 'Admin · Gallery')

@section('content')
    <div class="admin-page-heading gallery-page-heading">
        <div>
            <p class="step-label">Website content</p>
            <h1>Gallery</h1>
            <p class="muted">Manage the photos visitors see when they explore the clinic.</p>
        </div>
        <div class="admin-date-controls">
            <span class="gallery-publish-status"><span aria-hidden="true"></span> Published to homepage</span>
        </div>
    </div>

    <section class="admin-panel gallery-upload-panel">
        <div class="gallery-upload-heading">
            <span class="gallery-upload-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5"/><path d="M5 13v5a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5"/></svg>
            </span>
            <div>
                <p class="step-label">Add new photo</p>
                <h2>Share a clinic moment</h2>
                <p class="muted small">Photos are published to your public homepage as soon as they’re added.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="gallery-upload-form">
            @csrf
            <div class="field gallery-file-field">
                <label for="image">Choose an image</label>
                <input class="gallery-file-input" id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required>
                <span class="gallery-field-hint">JPG, PNG, or WebP · Up to 5 MB</span>
                @error('image') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="alt_text">Image description <span class="gallery-label-note">Required for accessibility</span></label>
                <input id="alt_text" name="alt_text" type="text" maxlength="255" value="{{ old('alt_text') }}" required>
                @error('alt_text') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label for="caption">Photo caption <span class="gallery-label-note">Optional</span></label>
                <input id="caption" name="caption" type="text" maxlength="500" value="{{ old('caption') }}">
                @error('caption') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="gallery-upload-actions">
                <button class="btn btn-primary" type="submit">Publish photo</button>
                <span class="muted small">Your image will be visible to everyone.</span>
            </div>
        </form>
    </section>

    <section class="gallery-admin-section">
        <div class="gallery-admin-heading">
            <div>
                <p class="step-label">Homepage content</p>
                <h2>Published photos</h2>
            </div>
            <span class="gallery-image-count">{{ $galleryImages->count() }} {{ \Illuminate\Support\Str::plural('photo', $galleryImages->count()) }}</span>
        </div>

        @if ($galleryImages->isNotEmpty())
            <div class="gallery-admin-grid">
                @foreach ($galleryImages as $galleryImage)
                    <article class="gallery-admin-card">
                        <div class="gallery-admin-image">
                            <img src="{{ Storage::disk('public')->url($galleryImage->path) }}" alt="{{ $galleryImage->alt_text }}">
                            <span class="gallery-photo-status"><span aria-hidden="true"></span> Live</span>
                        </div>
                        <div class="gallery-admin-copy">
                            <strong class="gallery-photo-title">{{ $galleryImage->caption ?: $galleryImage->alt_text }}</strong>
                            @if ($galleryImage->caption)
                                <span class="gallery-photo-description">{{ $galleryImage->alt_text }}</span>
                            @endif
                            <form method="POST" action="{{ route('admin.gallery.destroy', $galleryImage) }}" data-confirm="Remove this image from the gallery?">
                                @csrf
                                @method('DELETE')
                                <button class="gallery-remove-button" type="submit">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg>
                                    Remove photo
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="gallery-placeholder">
                <span class="gallery-placeholder-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/></svg>
                </span>
                <div>
                    <strong>Your gallery is ready for its first photo</strong>
                    <p>Upload a photo above and it will appear on your homepage right away.</p>
                </div>
            </div>
        @endif
    </section>
@endsection

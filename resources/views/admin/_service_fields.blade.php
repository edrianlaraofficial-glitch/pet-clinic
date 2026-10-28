<div class="form-grid">
    <div class="field">
        <label for="service-name-{{ $service?->id ?? 'new' }}">Name</label>
        <input id="service-name-{{ $service?->id ?? 'new' }}" type="text" name="name" value="{{ old('name', $service->name ?? '') }}" required maxlength="120">
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="service-duration-{{ $service?->id ?? 'new' }}">Duration (minutes)</label>
        <input id="service-duration-{{ $service?->id ?? 'new' }}" type="number" name="duration_minutes" min="10" max="480" step="5" value="{{ old('duration_minutes', $service->duration_minutes ?? 30) }}" required>
        @error('duration_minutes') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="service-price-{{ $service?->id ?? 'new' }}">Price (₱)</label>
        <input id="service-price-{{ $service?->id ?? 'new' }}" type="number" name="price" min="0" step="0.01" value="{{ old('price', $service->price ?? '') }}" required>
        @error('price') <span class="field-error">{{ $message }}</span> @enderror
    </div>
</div>
<div class="field">
    <label for="service-description-{{ $service?->id ?? 'new' }}">Description</label>
    <textarea id="service-description-{{ $service?->id ?? 'new' }}" name="description" maxlength="1000">{{ old('description', $service->description ?? '') }}</textarea>
    @error('description') <span class="field-error">{{ $message }}</span> @enderror
</div>
@unless ($service)
    <label class="checkbox admin-service-bookable"><input type="checkbox" name="is_active" value="1" checked> Bookable online</label>
@endunless

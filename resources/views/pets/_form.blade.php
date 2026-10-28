<div class="form-grid">
    <div class="field">
        <label for="pet-name">Dog's name</label>
        <input id="pet-name" type="text" name="name" value="{{ old('name', $pet->name ?? '') }}" required maxlength="80">
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="pet-breed">Breed <span class="muted small">(optional)</span></label>
        <input id="pet-breed" type="text" name="breed" value="{{ old('breed', $pet->breed ?? '') }}" maxlength="80">
        @error('breed') <span class="field-error">{{ $message }}</span> @enderror
    </div>
    <div class="field">
        <label for="pet-dob">Date of birth <span class="muted small">(optional)</span></label>
        <input id="pet-dob" type="date" name="date_of_birth" value="{{ old('date_of_birth', isset($pet) && $pet->date_of_birth ? $pet->date_of_birth->toDateString() : '') }}" max="{{ now()->toDateString() }}">
        @error('date_of_birth') <span class="field-error">{{ $message }}</span> @enderror
    </div>
</div>
<div class="field">
    <label for="pet-notes">Notes for the vet <span class="muted small">(allergies, temperament, medication)</span></label>
    <textarea id="pet-notes" name="notes" maxlength="1000">{{ old('notes', $pet->notes ?? '') }}</textarea>
    @error('notes') <span class="field-error">{{ $message }}</span> @enderror
</div>

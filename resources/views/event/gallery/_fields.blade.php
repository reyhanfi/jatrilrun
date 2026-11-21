<div class="field-group">
    <label for="title">Judul Foto</label>
    <input type="text" id="title" name="title" value="{{ old('title', $photo?->title) }}" maxlength="200" required>
    @error('title')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror
</div>
<div class="field-group">
    <label for="description">Keterangan</label>
    <textarea id="description" name="description" rows="3" maxlength="1000">{{ old('description', $photo?->description) }}</textarea>
    @error('description')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror
</div>
<div class="field-group">
    <label for="gallery-image">{{ $photo ? 'Ganti Foto' : 'Pilih Foto' }}</label>
    <input type="file" id="gallery-image" name="image" accept="image/jpeg,image/png,image/webp"
        aria-describedby="gallery-image-help" @required(!$photo)>
    <p id="gallery-image-help" class="section-note">
        JPG, PNG, atau WebP, maksimal 2 MB.
        @if ($photo)
            Kosongkan untuk memakai foto saat ini.
        @endif
    </p>
    @error('image')
        <p class="error" role="alert">{{ $message }}</p>
    @enderror
</div>

@extends('auth.layouts')

@section('title', 'Edit Event')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-gallery.css') }}">
@endpush

@section('content')
    <div class="card" style="max-width: 760px;">
        <div class="eyebrow">Perbarui Event</div>
        <h1>Edit Event</h1>
        <p>Periksa kembali detail event sebelum menyimpan perubahan.</p>

        <form action="{{ route('event.update', $event->id) }}" method="POST" class="event-form">
            @csrf
            @method('PUT')

            @include('event._form')

            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 22px;">
                <button type="submit" class="primary-button">Simpan Perubahan</button>
                <a href="{{ route('event.index') }}" class="ghost-button">Kembali ke daftar</a>
            </div>
        </form>
    </div>

    <section class="card gallery-edit event-section">
        <div class="gallery-heading">
            <h2>Foto Sampul</h2>
            <a class="ghost-button" href="{{ route('event.gallery.index', $event) }}">Kelola Galeri</a>
        </div>

        @if ($event->cover_path)
            <img class="gallery-preview" src="{{ asset('storage/'.$event->cover_path) }}"
                alt="Foto sampul {{ $event->nama_event }}" width="640" height="480">
        @endif

        <form action="{{ route('event.cover.update', $event) }}" method="POST" enctype="multipart/form-data" class="gallery-form">
            @csrf
            @method('PUT')
            <div class="field-group">
                <label for="cover-image">{{ $event->cover_path ? 'Ganti Sampul' : 'Pilih Sampul' }}</label>
                <input type="file" id="cover-image" name="image" accept="image/jpeg,image/png,image/webp"
                    aria-describedby="cover-image-help" required>
                <p id="cover-image-help" class="section-note">JPG, PNG, atau WebP, maksimal 2 MB.</p>
                @error('image')
                    <p class="error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="primary-button">Simpan Sampul</button>
        </form>
    </section>
@endsection

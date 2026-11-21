@extends('auth.layouts')

@section('title', 'Kelola Galeri')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-gallery.css') }}">
@endpush

@section('content')
    <header class="page-header">
        <div>
            <h1>Kelola Galeri</h1>
            <p>{{ $event->nama_event }}</p>
        </div>
        <div class="header-actions">
            <a class="ghost-button" href="{{ route('event.edit', $event) }}">Edit Event</a>
            <a class="secondary-button" href="{{ route('event.show', ['event' => $event->slug]) }}#galeri">Lihat Galeri Publik</a>
        </div>
    </header>

    <div class="gallery-admin">
        <section class="panel">
            <h2>Unggah Foto</h2>
            <form action="{{ route('event.gallery.store', $event) }}" method="POST" enctype="multipart/form-data" class="gallery-form">
                @csrf
                @include('event.gallery._fields', ['photo' => null])
                <button type="submit" class="primary-button">Unggah Foto</button>
            </form>
        </section>

        <section class="panel">
            <h2>Foto Dokumentasi</h2>
            <div class="gallery-grid">
                @forelse ($photos as $photo)
                    <article class="gallery-item">
                        <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->title }}" loading="lazy" width="640" height="480">
                        <div class="gallery-item-body">
                            <h3>{{ $photo->title }}</h3>
                            @if ($photo->description)
                                <p>{{ $photo->description }}</p>
                            @endif
                            <div class="table-actions">
                                <a class="mini-button edit" href="{{ route('event.gallery.edit', [$event, $photo]) }}">Edit</a>
                                <form action="{{ route('event.gallery.destroy', [$event, $photo]) }}" method="POST"
                                    onsubmit="return confirm('Hapus foto ini dari galeri?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mini-button delete">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="section-note">Belum ada foto dokumentasi.</p>
                @endforelse
            </div>
            {{ $photos->links('pagination') }}
        </section>
    </div>
@endsection

@extends('auth.layouts')

@section('title', e($event->nama_event))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-gallery.css') }}">
    @if ($photos->isNotEmpty())
        <link rel="stylesheet" href="{{ asset('vendor/lightbox2/css/lightbox.min.css') }}">
    @endif
@endpush

@if ($photos->isNotEmpty())
    @push('scripts')
        <script src="{{ asset('vendor/lightbox2/js/lightbox-plus-jquery.min.js') }}"></script>
        <script>lightbox.option({sanitizeTitle: true, wrapAround: true});</script>
    @endpush
@endif

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Detail Event</p>
            <h1>{{ $event->nama_event }}</h1>
        </div>
        <a class="ghost-button" href="{{ route('event.catalog') }}">Kembali ke Daftar</a>
    </section>

    @if ($event->cover_path)
        <img class="event-cover" src="{{ asset('storage/'.$event->cover_path) }}"
            alt="Foto sampul {{ $event->nama_event }}" width="1120" height="400">
    @endif

    <div class="panel">
        <dl class="event-details">
            <div>
                <dt>Penyelenggara</dt>
                <dd>{{ $event->penyelenggara }}</dd>
            </div>
            <div>
                <dt>Tipe Event</dt>
                <dd>{{ $event->tipe_event }}</dd>
            </div>
            <div>
                <dt>Jadwal</dt>
                <dd>{{ $event->tanggal_mulai->format('d-m-Y') }} sampai {{ $event->tanggal_selesai->format('d-m-Y') }}</dd>
            </div>
            <div>
                <dt>Slot Peserta</dt>
                <dd>{{ number_format($event->slot_peserta, 0, ',', '.') }} peserta</dd>
            </div>
        </dl>
    </div>

    <section id="galeri" class="panel event-section">
        <div class="gallery-heading">
            <h2>Galeri Dokumentasi</h2>
            @if (auth()->user()?->level === 'admin')
                <a class="secondary-button" href="{{ route('event.gallery.index', $event) }}">Kelola Galeri</a>
            @endif
        </div>

        @if ($photos->isNotEmpty())
            <div class="gallery-grid">
                @foreach ($photos as $photo)
                    <figure class="gallery-item">
                        <a href="{{ asset('storage/'.$photo->path) }}" data-lightbox="event-{{ $event->id }}"
                            data-title="{{ $photo->title }}">
                            <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->title }}"
                                loading="lazy" width="640" height="480">
                        </a>
                        <figcaption>
                            <strong>{{ $photo->title }}</strong>
                            @if ($photo->description)
                                <p>{{ $photo->description }}</p>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
            {{ $photos->links('pagination') }}
        @else
            <p class="section-note">Belum ada foto dokumentasi.</p>
        @endif
    </section>

    <section id="pendaftaran" class="panel event-section">
        <h2>Pendaftaran Peserta</h2>
        @guest
            <a class="primary-button" href="{{ route('event.registrations.create', ['event' => $event->slug]) }}">
                Login untuk Daftar Event
            </a>
        @elseif ($registration)
            <p>Status pendaftaran: <strong>{{ $registration->status }}</strong></p>
        @else
            <form action="{{ route('event.registrations.store', $event) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="field-group">
                    <label for="ktp">Unggah Foto KTP</label>
                    <input type="file" id="ktp" name="ktp" accept="image/jpeg,image/png" required aria-describedby="ktp-help">
                    <p id="ktp-help" class="section-note">JPG atau PNG, maksimal 2 MB.</p>
                    @error('ktp')
                        <div class="error" role="alert">{{ $message }}</div>
                    @enderror
                </div>
                <button class="primary-button" type="submit">Daftar Event</button>
            </form>
        @endguest
    </section>
@endsection

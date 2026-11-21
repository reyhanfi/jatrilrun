@extends('auth.layouts')

@section('title', 'Jelajahi Event')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-gallery.css') }}">
@endpush

@section('content')
    <header class="page-header">
        <h1>Jelajahi Event</h1>
    </header>

    <div class="catalog-grid">
        @forelse ($events as $event)
            <article class="catalog-item">
                <a href="{{ route('event.show', ['event' => $event->slug]) }}">
                    @if ($event->cover_path)
                        <img class="catalog-cover" src="{{ asset('storage/'.$event->cover_path) }}"
                            alt="" loading="lazy" width="640" height="360">
                    @else
                        <div class="catalog-cover catalog-placeholder" aria-hidden="true"></div>
                    @endif
                    <div class="catalog-information">
                        <p class="catalog-type">{{ $event->tipe_event }}</p>
                        <h2>{{ $event->nama_event }}</h2>
                        <p>{{ $event->tanggal_mulai->format('d/m/Y') }}
                            @if (!$event->tanggal_mulai->isSameDay($event->tanggal_selesai))
                                – {{ $event->tanggal_selesai->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </a>
            </article>
        @empty
            <p class="section-note">Belum ada event.</p>
        @endforelse
    </div>

    {{ $events->links('pagination') }}
@endsection

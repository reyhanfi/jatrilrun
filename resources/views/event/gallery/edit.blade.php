@extends('auth.layouts')

@section('title', 'Edit Foto')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/event-gallery.css') }}">
@endpush

@section('content')
    <div class="card gallery-edit">
        <div class="eyebrow">{{ $event->nama_event }}</div>
        <h1>Edit Foto</h1>
        <img class="gallery-preview" src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->title }}" width="640" height="480">

        <form action="{{ route('event.gallery.update', [$event, $photo]) }}" method="POST" enctype="multipart/form-data" class="gallery-form">
            @csrf
            @method('PUT')
            @include('event.gallery._fields')

            <div class="header-actions">
                <button type="submit" class="primary-button">Simpan Perubahan</button>
                <a class="ghost-button" href="{{ route('event.gallery.index', $event) }}">Kembali ke Galeri</a>
            </div>
        </form>
    </div>
@endsection

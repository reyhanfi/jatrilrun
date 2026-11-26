@extends('auth.layouts')

@section('title', 'Login')

@section('content')
    <div class="card">
        <h1>Login</h1>
        <p>Masuk untuk melihat jadwal dan mengelola pendaftaran event.</p>

        <a href="{{ route('google.redirect') }}" class="ghost-button" style="width: 100%; gap: 10px;">
            <img src="{{ asset('images/google-g.png') }}" width="18" height="18" alt="">
            Masuk dengan Google Kampus
        </a>
        <p class="section-note">Gunakan akun Google dengan alamat @mail.ugm.ac.id.</p>
        @error('google')
            <div class="error" role="alert">{{ $message }}</div>
        @enderror

        <form action="{{ route('authenticate') }}" method="POST" class="auth-form">
            @csrf

            <div class="field-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <button class="primary-button" type="submit" style="width: 100%; margin-top: 8px;">Login</button>
        </form>

        <p class="auth-meta">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
    </div>
@endsection

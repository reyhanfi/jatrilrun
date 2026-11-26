@extends('auth.layouts')

@section('title', 'Profil')

@push('styles')
    <style>
        .profile-welcome { margin: 0 0 18px; }
        .profile-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
            gap: 18px;
            align-items: start;
        }
        .profile-card { text-align: center; }
        .profile-card h1 { font-size: 1.35rem; margin: 12px 0 4px; overflow-wrap: anywhere; }
        .profile-email { margin: 0 0 12px; overflow-wrap: anywhere; }
        .profile-notifications h2 { font-size: 1.15rem; margin: 0; }
        @media (max-width: 650px) {
            .profile-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php($user = auth()->user())
    <p class="message profile-welcome">Selamat datang, <strong>{{ $user->name }}</strong>!</p>

    <div class="profile-grid">
        <section class="panel profile-card" aria-label="Profil akun">
            <x-account-avatar :user="$user" :size="80" />
            <h1>{{ $user->name }}</h1>
            <p class="section-note profile-email">{{ $user->email }}</p>
            <span class="nav-count">{{ $user->level === 'admin' ? 'Admin' : 'Runner' }}</span>
        </section>

        <section id="notifikasi" class="panel profile-notifications" aria-labelledby="notification-title">
            <h2 id="notification-title">Notifikasi</h2>
            @if ($notifications && $notifications->isNotEmpty())
                <ul class="notification-list">
                    @foreach ($notifications as $notification)
                        <li>
                            <strong>{{ $notification->data['message'] }}</strong>
                            @if ($notification->unread())
                                <span class="nav-count">Baru</span>
                            @endif
                            <p>{{ $notification->data['name'] }} · {{ $notification->data['email'] }}</p>
                            <time datetime="{{ $notification->created_at->toIso8601String() }}">
                                {{ $notification->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                            </time>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="section-note">Belum ada notifikasi.</p>
            @endif
            @if ($notifications)
                {{ $notifications->links('pagination') }}
            @endif
        </section>
    </div>
@endsection

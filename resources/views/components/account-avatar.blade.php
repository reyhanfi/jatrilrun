@props(['user', 'size' => 32])

<span class="account-avatar" style="--avatar-size: {{ $size }}px;" aria-hidden="true">
    <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($user->name), 0, 1)) ?: '?' }}</span>
    @if ($user->google_avatar_url)
        <img src="{{ $user->google_avatar_url }}" alt="" width="{{ $size }}" height="{{ $size }}"
            referrerpolicy="no-referrer" onerror="this.hidden = true">
    @endif
</span>

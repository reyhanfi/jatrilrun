<x-mail::message>
# Selamat datang, {{ $name }}!

Akun kampusmu sudah aktif di Jatrilrun. Kamu bisa melihat jadwal dan mendaftar kegiatan lari yang tersedia.

<x-mail::button :url="route('dashboard')">
Buka Dashboard
</x-mail::button>

Tim Jatrilrun
</x-mail::message>

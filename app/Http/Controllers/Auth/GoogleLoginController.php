<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\KirimEmailSelamatDatang;
use App\Models\User;
use App\Notifications\UserBaruNotification;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleLoginController extends Controller
{
    private const CAMPUS_DOMAIN = 'mail.ugm.ac.id';

    public function redirect(): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return $this->loginError('Login Google belum dikonfigurasi.');
        }

        return Socialite::driver('google')
            ->with(['hd' => self::CAMPUS_DOMAIN])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            $request->session()->forget('state');

            return $this->loginError('Login Google dibatalkan atau ditolak.');
        }

        try {
            $profile = Socialite::driver('google')->user();
        } catch (InvalidStateException|GuzzleException) {
            return $this->loginError('Sesi login Google tidak dapat diverifikasi. Silakan coba lagi.');
        }

        $email = Str::lower(trim((string) $profile->getEmail()));
        $claims = $profile->getRaw();
        $subject = (string) $profile->getId();
        if ($subject === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! str_ends_with($email, '@'.self::CAMPUS_DOMAIN)
            || ($claims['hd'] ?? null) !== self::CAMPUS_DOMAIN
            || ($claims['email_verified'] ?? false) !== true) {
            return $this->loginError('Gunakan akun Google kampus terverifikasi @mail.ugm.ac.id.');
        }

        $avatar = $this->googleAvatar($profile->getAvatar());

        try {
            $user = DB::transaction(function () use ($profile, $subject, $email, $avatar) {
                $user = User::query()->where('google_id', $subject)->first();
                if ($user) {
                    $user->google_avatar_url = $avatar;
                    if ($user->email !== $email) {
                        $user->forceFill(['email' => $email, 'email_verified_at' => now()]);
                    }
                    if ($user->isDirty()) {
                        $user->save();
                    }

                    return $user;
                }

                if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                    return null;
                }

                $user = new User([
                    'name' => Str::limit($profile->getName() ?: Str::before($email, '@'), 255, ''),
                    'email' => $email,
                    'password' => Str::random(64),
                    'level' => 'user',
                ]);
                $user->forceFill([
                    'google_id' => $subject,
                    'google_avatar_url' => $avatar,
                    'email_verified_at' => now(),
                ])->save();

                KirimEmailSelamatDatang::dispatch($user)->afterCommit();
                Notification::send(User::query()->where('level', 'admin')->get(), new UserBaruNotification($user));

                return $user;
            });
        } catch (UniqueConstraintViolationException) {
            $user = User::query()->where('google_id', $subject)->where('email', $email)->first();
        }

        if (! $user) {
            return $this->loginError('Email ini sudah digunakan. Hubungi admin untuk menghubungkan akun.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('status', 'Login Google berhasil.');
    }

    private function googleAvatar(?string $url): ?string
    {
        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        return ($parts['scheme'] ?? '') === 'https'
            && ! isset($parts['user']) && ! isset($parts['port'])
            && str_ends_with($host, '.googleusercontent.com')
            ? $url : null;
    }

    private function loginError(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['google' => $message]);
    }
}

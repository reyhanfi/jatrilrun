<?php

namespace App\Listeners;

use App\Mail\LoginBaruMail;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class KirimNotifikasiLogin
{
    public function __construct(private readonly Request $request) {}

    public function handle(Login $event): void
    {
        if ($event->guard !== 'web') {
            return;
        }

        Mail::to($event->user->email)->queue(new LoginBaruMail(
            name: $event->user->name,
            loginTime: now('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB',
            ipAddress: $this->request->ip() ?? 'Tidak tersedia',
        ));
    }
}

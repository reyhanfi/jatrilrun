<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->level === 'admin' ? $user->notifications()->paginate(8) : null;

        return view('auth.dashboard', compact('notifications'));
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi sudah dibaca.');
    }
}

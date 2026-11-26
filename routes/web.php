<?php

use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LoginRegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventGalleryController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\EventSpreadsheetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::get('/events', [EventController::class, 'catalog'])->name('event.catalog');

Route::middleware('guest')->controller(GoogleLoginController::class)->group(function () {
    Route::get('/auth/google/redirect', 'redirect')->name('google.redirect');
    Route::get('/auth/google/callback', 'callback')->name('google.callback');
});

Route::middleware('guest')
    ->controller(LoginRegisterController::class)
    ->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/store', 'store')->name('store');

        Route::get('/login', 'login')->name('login');
        Route::post('/authenticate', 'authenticate')->name('authenticate');
    });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::controller(LoginRegisterController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
    });

    Route::controller(EventController::class)->group(function () {
        Route::get('/event/search', 'search')->name('event.search');
        Route::get('/event', 'index')->name('event.index');
        Route::middleware('admin')->group(function () {
            Route::get('/event/create', 'create')->name('event.create');
            Route::post('/event', 'store')->name('event.store');
            Route::get('/event/{event}/edit', 'edit')->name('event.edit');
            Route::put('/event/{event}', 'update')->name('event.update');
            Route::delete('/event/{event}', 'destroy')->name('event.destroy');
        });
    });

    Route::post('/event/{event}/registrations', [EventRegistrationController::class, 'store'])
        ->name('event.registrations.store');
    Route::get('/event/{event:slug}/register', [EventRegistrationController::class, 'create'])
        ->name('event.registrations.create');

    Route::middleware('admin')->group(function () {
        Route::post('/notifikasi/baca-semua', [DashboardController::class, 'bacaSemua'])
            ->name('notifications.read-all');
        Route::resource('users', UserController::class);
        Route::get('/event/{event}/registrations', [EventRegistrationController::class, 'index'])
            ->name('event.registrations.index');
        Route::get('/event/{event}/registrations/export', [EventRegistrationController::class, 'export'])
            ->name('event.registrations.export');
        Route::patch('/registrations/{registration}/status', [EventRegistrationController::class, 'update'])
            ->name('registrations.update');
        Route::get('/registrations/{registration}/ktp', [EventRegistrationController::class, 'download'])
            ->name('registrations.ktp');
        Route::post('/event/import', [EventSpreadsheetController::class, 'import'])->name('event.import');
        Route::get('/event/import/template', [EventSpreadsheetController::class, 'template'])->name('event.import.template');

        Route::controller(EventGalleryController::class)
            ->prefix('event/{event}')
            ->whereNumber('event')
            ->scopeBindings()
            ->group(function () {
                Route::get('/gallery', 'index')->name('event.gallery.index');
                Route::post('/gallery', 'store')->name('event.gallery.store');
                Route::get('/gallery/{photo}/edit', 'edit')->name('event.gallery.edit');
                Route::put('/gallery/{photo}', 'update')->name('event.gallery.update');
                Route::delete('/gallery/{photo}', 'destroy')->name('event.gallery.destroy');
                Route::put('/cover', 'updateCover')->name('event.cover.update');
            });
    });
});

Route::get('/event/{legacyEvent}', [EventController::class, 'redirectToSlug'])
    ->whereNumber('legacyEvent')->name('event.legacy');
Route::get('/event/{event:slug}', [EventController::class, 'show'])
    ->where('event', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('event.show');

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('tipe_event') &&
            $request->tipe_event !== 'Semua Tipe') {
            $query->where('tipe_event', $request->tipe_event);
        }

        if ($request->filled('keyword')) {
            $query->where(
                'nama_event',
                'like',
                '%' . $request->keyword . '%'
            );
        }

        $events = (clone $query)
            ->orderBy('tanggal_mulai')
            ->paginate(5)
            ->withQueryString();

        $total_event = Event::count();
        $total_run = Event::where('tipe_event', 'Run')->count();
        $total_trail_run = Event::where('tipe_event', 'Trail Run')->count();
        $total_challenge = Event::where('tipe_event', 'Challenge')->count();
        $total_slot_peserta = (int) Event::sum('slot_peserta');

        $tipe_event = $request->tipe_event;
        $keyword = $request->keyword;

        return view('event', compact(
            'events',
            'tipe_event',
            'keyword',
            'total_event',
            'total_run',
            'total_trail_run',
            'total_challenge',
            'total_slot_peserta'
        ));
    }

    public function catalog()
    {
        $events = Event::orderByDesc('tanggal_mulai')->orderByDesc('id')->paginate(12);
        $pageDescription = 'Jelajahi jadwal event lari dan dokumentasinya di Jatrilrun.';

        return view('event.catalog', compact('events', 'pageDescription'));
    }

    public function redirectToSlug(Event $legacyEvent)
    {
        return redirect()->route('event.show', ['event' => $legacyEvent->slug], 301);
    }

    public function show(Request $request, Event $event)
    {
        $user = $request->user();
        $registration = $user ? $event->registrations()->where('user_id', $user->id)->first() : null;
        $photos = $event->photos()->latest('id')->paginate(12)->fragment('galeri');

        $pageDescription = sprintf(
            'Jadwal dan dokumentasi %s, event %s oleh %s pada %s.',
            $event->nama_event,
            $event->tipe_event,
            $event->penyelenggara,
            $event->tanggal_mulai->format('d/m/Y')
        );
        $canonicalUrl = route('event.show', ['event' => $event->slug]);

        return view('event.show', compact('event', 'registration', 'photos', 'pageDescription', 'canonicalUrl'));
    }

    public function create()
    {
        return view('event.create', ['event' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            Event::validationRules(),
            $this->messages()
        );

        Event::create($validated);

        return redirect()->route('event.index')
            ->with('pesan', 'Event berhasil ditambahkan.');
    }

    public function edit(Event $event)
    {
        return view('event.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate(
            Event::validationRules(),
            $this->messages()
        );

        $event->update($validated);

        return redirect()->route('event.index')
            ->with('pesan', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        try {
            $paths = DB::transaction(function () use ($event) {
                $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->id);
                $paths = $lockedEvent->photos()->pluck('path');
                if ($lockedEvent->cover_path) {
                    $paths->push($lockedEvent->cover_path);
                }
                $lockedEvent->delete();

                return $paths->all();
            });
        } catch (QueryException $exception) {
            if (!$event->registrations()->exists()) {
                throw $exception;
            }

            return redirect()->route('event.index')
                ->with('pesan', 'Event yang memiliki pendaftaran tidak dapat dihapus.');
        }

        Storage::disk('public')->delete($paths);

        return redirect()->route('event.index')
            ->with('pesan', 'Event berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $hasil = Event::query()
            ->when($keyword, function ($query, $keyword) {
                $query->where(
                    'nama_event',
                    'like',
                    '%' . $keyword . '%'
                );
            })
            ->orderBy('tanggal_mulai')
            ->paginate(5)
            ->withQueryString();

        return view('event.search', compact('hasil', 'keyword'));
    }

    private function messages(): array
    {
        return [
            'nama_event.required' => 'Nama event wajib diisi.',
            'nama_event.max' => 'Nama event maksimal 255 karakter.',
            'penyelenggara.required' => 'Nama penyelenggara wajib diisi.',
            'penyelenggara.max' => 'Nama penyelenggara maksimal 255 karakter.',
            'tipe_event.required' => 'Tipe event wajib dipilih.',
            'tipe_event.in' => 'Tipe event yang dipilih tidak valid.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' =>
                'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'slot_peserta.required' => 'Slot peserta wajib diisi.',
            'slot_peserta.integer' => 'Slot peserta harus berupa bilangan bulat.',
            'slot_peserta.min' => 'Slot peserta minimal 1.',
            'slot_peserta.max' => 'Slot peserta maksimal 1.000.000.',
        ];
    }
}

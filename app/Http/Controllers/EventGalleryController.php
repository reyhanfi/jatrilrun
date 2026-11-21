<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPhoto;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class EventGalleryController extends Controller
{
    private const IMAGE_RULE = 'image|mimes:jpg,jpeg,png,webp|max:2048';

    public function index(Event $event)
    {
        $photos = $event->photos()->latest('id')->paginate(12);

        return view('event.gallery.index', compact('event', 'photos'));
    }

    public function store(Request $request, Event $event)
    {
        $details = $this->validatePhoto($request, 'required');
        $this->saveImage($event, $request->file('image'), function (Event $event, string $path) use ($details) {
            $event->photos()->create($details + ['path' => $path]);
        });

        return redirect()->route('event.gallery.index', $event)->with('status', 'Foto berhasil ditambahkan.');
    }

    public function edit(Event $event, EventPhoto $photo)
    {
        return view('event.gallery.edit', compact('event', 'photo'));
    }

    public function update(Request $request, Event $event, EventPhoto $photo)
    {
        $details = $this->validatePhoto($request, 'nullable');
        if ($request->hasFile('image')) {
            $this->saveImage($event, $request->file('image'), function (Event $event, string $path) use ($photo, $details) {
                $photo = $event->photos()->findOrFail($photo->id);
                $oldPath = $photo->path;
                $photo->update($details + ['path' => $path]);

                return $oldPath;
            });
        } else {
            $photo->update($details);
        }

        return redirect()->route('event.gallery.index', $event)->with('status', 'Foto berhasil diperbarui.');
    }

    public function destroy(Event $event, EventPhoto $photo)
    {
        $path = DB::transaction(function () use ($event, $photo) {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            $photo = $event->photos()->findOrFail($photo->id);
            $photo->delete();

            return $photo->path;
        });
        Storage::disk('public')->delete($path);

        return redirect()->route('event.gallery.index', $event)->with('status', 'Foto berhasil dihapus.');
    }

    public function updateCover(Request $request, Event $event)
    {
        $request->validate(['image' => 'required|'.self::IMAGE_RULE], $this->imageMessages());

        $this->saveImage($event, $request->file('image'), function (Event $event, string $path) {
            $oldPath = $event->cover_path;
            $event->cover_path = $path;
            $event->save();

            return $oldPath;
        });

        return redirect()->route('event.edit', $event)->with('status', 'Foto sampul berhasil diperbarui.');
    }

    private function validatePhoto(Request $request, string $presence): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'image' => $presence.'|'.self::IMAGE_RULE,
        ], $this->imageMessages() + [
            'title.required' => 'Judul foto wajib diisi.',
            'title.max' => 'Judul foto maksimal 200 karakter.',
            'description.max' => 'Keterangan foto maksimal 1.000 karakter.',
        ]);

        unset($validated['image']);

        return $validated;
    }

    private function imageMessages(): array
    {
        return [
            'image.required' => 'Pilih foto yang ingin diunggah.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Gunakan foto JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
            'image.uploaded' => 'Foto gagal diunggah. Periksa ukuran berkas dan coba lagi.',
        ];
    }

    private function saveImage(Event $event, UploadedFile $image, Closure $save): void
    {
        $path = $image->store("event-media/{$event->id}", 'public');
        if ($path === false) {
            throw ValidationException::withMessages(['image' => 'Foto gagal disimpan. Silakan coba lagi.']);
        }

        try {
            $oldPath = DB::transaction(function () use ($event, $path, $save) {
                $event = Event::query()->lockForUpdate()->findOrFail($event->id);

                return $save($event, $path);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }
    }
}

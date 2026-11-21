<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    public $timestamps = false;

    protected $fillable = [
        'nama_event',
        'penyelenggara',
        'tipe_event',
        'tanggal_mulai',
        'tanggal_selesai',
        'slot_peserta',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            $event->slug = self::uniqueSlug($event->nama_event);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = rtrim(substr(Str::slug($name), 0, 170), '-') ?: 'event';
        if (ctype_digit($base) || in_array($base, ['create', 'search', 'import'], true)) {
            $base = 'event-'.$base;
        }

        $slug = $base;
        for ($number = 2; self::query()->where('slug', $slug)->exists(); $number++) {
            $slug = $base.'-'.$number;
        }

        return $slug;
    }

    public static function validationRules(): array
    {
        return [
            'nama_event' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'tipe_event' => 'required|in:Run,Trail Run,Challenge',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'slot_peserta' => 'required|integer|min:1|max:1000000',
        ];
    }

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'slot_peserta' => 'integer',
        ];
    }
}

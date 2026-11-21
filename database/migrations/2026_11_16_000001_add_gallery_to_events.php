<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 191)->nullable();
            $table->string('cover_path')->nullable();
        });

        DB::table('events')->select('id', 'nama_event')->chunkById(100, function ($events) {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)
                    ->update(['slug' => Event::uniqueSlug($event->nama_event)]);
            }
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('slug', 191)->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'cover_path']);
        });
    }
};

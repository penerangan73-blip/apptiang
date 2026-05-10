<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pole_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pole_id')->constrained()->cascadeOnDelete();
            $table->string('photo_url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('poles')
            ->whereNotNull('photo_url')
            ->where('photo_url', '!=', '')
            ->orderBy('id')
            ->get(['id', 'photo_url', 'created_at', 'updated_at'])
            ->each(function (object $pole): void {
                DB::table('pole_photos')->insert([
                    'pole_id' => $pole->id,
                    'photo_url' => $pole->photo_url,
                    'sort_order' => 0,
                    'created_at' => $pole->created_at ?? now(),
                    'updated_at' => $pole->updated_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pole_photos');
    }
};

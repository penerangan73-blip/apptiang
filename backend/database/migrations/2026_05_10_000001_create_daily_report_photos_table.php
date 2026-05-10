<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->string('photo_url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('daily_reports')
            ->whereNotNull('photo_url')
            ->where('photo_url', '!=', '')
            ->orderBy('id')
            ->get(['id', 'photo_url', 'created_at', 'updated_at'])
            ->each(function (object $report): void {
                DB::table('daily_report_photos')->insert([
                    'daily_report_id' => $report->id,
                    'photo_url' => $report->photo_url,
                    'sort_order' => 0,
                    'created_at' => $report->created_at ?? now(),
                    'updated_at' => $report->updated_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_photos');
    }
};

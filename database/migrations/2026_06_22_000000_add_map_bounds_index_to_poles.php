<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            $table->index(['latitude', 'longitude'], 'poles_latitude_longitude_index');
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            $table->dropIndex('poles_latitude_longitude_index');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'lamp_house_type')) {
                $table->string('lamp_house_type')->default('caping')->after('lamp_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (Schema::hasColumn('poles', 'lamp_house_type')) {
                $table->dropColumn('lamp_house_type');
            }
        });
    }
};

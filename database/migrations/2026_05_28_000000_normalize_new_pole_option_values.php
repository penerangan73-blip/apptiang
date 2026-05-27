<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('poles', 'lamp_house_type')) {
            DB::table('poles')
                ->whereNull('lamp_house_type')
                ->update(['lamp_house_type' => 'caping']);
        }

        if (Schema::hasColumn('poles', 'lamp_house_condition')) {
            DB::table('poles')
                ->whereIn('lamp_house_condition', ['pecah', 'kusam', 'kemasukan_air'])
                ->orWhereNull('lamp_house_condition')
                ->update(['lamp_house_condition' => 'rusak']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('poles', 'lamp_house_condition')) {
            DB::table('poles')
                ->where('lamp_house_condition', 'rusak')
                ->update(['lamp_house_condition' => 'kusam']);
        }
    }
};

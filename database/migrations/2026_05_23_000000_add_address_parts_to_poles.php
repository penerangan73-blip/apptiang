<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'address')) {
                $table->text('address')->nullable()->after('street_name');
            }

            if (! Schema::hasColumn('poles', 'village')) {
                $table->string('village')->nullable()->index()->after('address');
            }

            if (! Schema::hasColumn('poles', 'district')) {
                $table->string('district')->nullable()->index()->after('village');
            }

            if (! Schema::hasColumn('poles', 'regency')) {
                $table->string('regency')->nullable()->index()->after('district');
            }

            if (! Schema::hasColumn('poles', 'province')) {
                $table->string('province')->nullable()->index()->after('regency');
            }
        });

        if (Schema::hasColumn('poles', 'address') && Schema::hasColumn('poles', 'street_name')) {
            DB::table('poles')
                ->whereNull('address')
                ->update(['address' => DB::raw('street_name')]);
        }
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            foreach (['province', 'regency', 'district', 'village', 'address'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

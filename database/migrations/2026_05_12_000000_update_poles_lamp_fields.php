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
            if (! Schema::hasColumn('poles', 'ornament_type')) {
                $table->string('ornament_type')->default('single_arm')->after('height');
            }

            if (! Schema::hasColumn('poles', 'lamp_type')) {
                $table->string('lamp_type')->default('led')->after('ornament_type');
            }

            if (! Schema::hasColumn('poles', 'lamp_status')) {
                $table->string('lamp_status')->default('menyala')->after('lamp_type');
            }

            if (! Schema::hasColumn('poles', 'lamp_house_condition')) {
                $table->string('lamp_house_condition')->default('kusam')->after('lamp_status');
            }

            if (! Schema::hasColumn('poles', 'light_point_count')) {
                $table->unsignedSmallInteger('light_point_count')->default(1)->after('lamp_house_condition');
            }

            if (! Schema::hasColumn('poles', 'kwh_meter')) {
                $table->string('kwh_meter')->default('belum')->after('light_point_count');
            }
        });

        if (Schema::hasColumn('poles', 'condition')) {
            DB::table('poles')->where('condition', 'baik')->update(['condition' => 'kokoh']);
            DB::table('poles')->where('condition', 'rusak')->update(['condition' => 'keropos']);
            DB::table('poles')->where('condition', 'perbaikan')->update(['condition' => 'berkarat']);
        }

        if (Schema::hasColumn('poles', 'pole_type')) {
            DB::table('poles')->whereIn('pole_type', ['lurus', 'lengkung', 'lainnya'])->update(['pole_type' => 'tiang_baja_galvanis']);
            DB::table('poles')->where('pole_type', 'tanpa_tiang')->update(['pole_type' => 'menempel_tiang_pln']);
        }

        if (Schema::hasColumn('poles', 'ornament_type')) {
            DB::table('poles')->where('ornament_type', 'tunggal')->update(['ornament_type' => 'single_arm']);
            DB::table('poles')->where('ornament_type', 'ganda')->update(['ornament_type' => 'double_arm']);
            DB::table('poles')->whereIn('ornament_type', ['dekoratif', 'lainnya'])->update(['ornament_type' => 'single_arm']);
        }

        if (Schema::hasColumn('poles', 'lamp_type')) {
            DB::table('poles')->where('lamp_type', 'sodium')->update(['lamp_type' => 'hps']);
        }

        if (Schema::hasColumn('poles', 'lamp_house_condition')) {
            DB::table('poles')->whereIn('lamp_house_condition', ['baik', 'rusak', 'hilang', 'lainnya'])->update(['lamp_house_condition' => 'kusam']);
        }

        if (Schema::hasColumn('poles', 'kwh_meter')) {
            DB::table('poles')->whereNull('kwh_meter')->update(['kwh_meter' => 'belum']);
        }

        Schema::table('poles', function (Blueprint $table) {
            foreach (['pln_transformer', 'foundation_type', 'span_length'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'span_length')) {
                $table->decimal('span_length', 8, 2)->nullable()->after('height');
            }

            if (! Schema::hasColumn('poles', 'foundation_type')) {
                $table->string('foundation_type')->default('beton')->after('span_length');
            }

            if (! Schema::hasColumn('poles', 'pln_transformer')) {
                $table->string('pln_transformer')->nullable()->after('ownership');
            }
        });

        Schema::table('poles', function (Blueprint $table) {
            foreach (['kwh_meter', 'light_point_count', 'lamp_house_condition', 'lamp_status', 'lamp_type', 'ornament_type'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (Schema::hasColumn('poles', 'pole_number') && ! Schema::hasColumn('poles', 'customer_id')) {
                $table->renameColumn('pole_number', 'customer_id');
            }

            if (Schema::hasColumn('poles', 'status') && ! Schema::hasColumn('poles', 'condition')) {
                $table->renameColumn('status', 'condition');
            }
        });

        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'pole_type')) {
                $table->string('pole_type')->default('lurus')->after('condition');
            }

            if (! Schema::hasColumn('poles', 'height')) {
                $table->decimal('height', 8, 2)->nullable()->after('pole_type');
            }

            if (! Schema::hasColumn('poles', 'span_length')) {
                $table->decimal('span_length', 8, 2)->nullable()->after('height');
            }

            if (! Schema::hasColumn('poles', 'foundation_type')) {
                $table->string('foundation_type')->default('beton')->after('span_length');
            }

            if (! Schema::hasColumn('poles', 'ownership')) {
                $table->string('ownership')->default('dpu_kp')->after('foundation_type');
            }

            if (! Schema::hasColumn('poles', 'pln_transformer')) {
                $table->string('pln_transformer')->nullable()->after('ownership');
            }

            if (! Schema::hasColumn('poles', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('pln_transformer');
            }

            if (! Schema::hasColumn('poles', 'notes')) {
                $table->text('notes')->nullable()->after('photo_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            foreach (['notes', 'photo_url', 'pln_transformer', 'ownership', 'foundation_type', 'span_length', 'height', 'pole_type'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('poles', function (Blueprint $table) {
            if (Schema::hasColumn('poles', 'customer_id') && ! Schema::hasColumn('poles', 'pole_number')) {
                $table->renameColumn('customer_id', 'pole_number');
            }

            if (Schema::hasColumn('poles', 'condition') && ! Schema::hasColumn('poles', 'status')) {
                $table->renameColumn('condition', 'status');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('updated_by_id')->index();
            }

            if (! Schema::hasColumn('poles', 'verified_by_id')) {
                $table->foreignId('verified_by_id')->nullable()->after('is_verified')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('poles', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (Schema::hasColumn('poles', 'verified_by_id')) {
                $table->dropForeign(['verified_by_id']);
            }

            foreach (['verified_at', 'verified_by_id', 'is_verified'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

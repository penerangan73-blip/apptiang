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
            if (! Schema::hasColumn('poles', 'created_by_id')) {
                $table->foreignId('created_by_id')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('poles', 'updated_by_id')) {
                $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
            }
        });

        $fallbackUserId = DB::table('users')
            ->whereIn('role', ['superadmin', 'admin'])
            ->value('id') ?? DB::table('users')->orderBy('id')->value('id');

        if ($fallbackUserId) {
            DB::table('poles')
                ->whereNull('created_by_id')
                ->update(['created_by_id' => $fallbackUserId]);

            DB::table('poles')
                ->whereNull('updated_by_id')
                ->update(['updated_by_id' => $fallbackUserId]);
        }
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (Schema::hasColumn('poles', 'updated_by_id')) {
                $table->dropConstrainedForeignId('updated_by_id');
            }

            if (Schema::hasColumn('poles', 'created_by_id')) {
                $table->dropConstrainedForeignId('created_by_id');
            }
        });
    }
};

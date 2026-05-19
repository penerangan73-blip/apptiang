<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            if (! Schema::hasColumn('poles', 'sub_name')) {
                $table->string('sub_name')->nullable()->after('customer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('poles', function (Blueprint $table) {
            foreach (['sub_name'] as $column) {
                if (Schema::hasColumn('poles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

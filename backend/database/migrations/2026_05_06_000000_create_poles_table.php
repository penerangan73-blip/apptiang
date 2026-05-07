<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poles', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('street_name')->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('power_usage');
            $table->string('condition')->default('baik')->index();
            $table->string('pole_type')->default('lurus');
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('span_length', 8, 2)->nullable();
            $table->string('foundation_type')->default('beton');
            $table->string('ownership')->default('dpu_kp');
            $table->string('pln_transformer')->nullable();
            $table->string('photo_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pole_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pole_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->timestamps();

            $table->index(['pole_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pole_comments');
    }
};

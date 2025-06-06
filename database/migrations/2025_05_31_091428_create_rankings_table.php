<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('map_id')->constrained()->onDelete('cascade');
            $table->bigInteger('score');
            $table->integer('max_combo');
            $table->decimal('accuracy', 5, 2);
            $table->string('grade')->nullable(); // S, A, B, C, D, F
            $table->timestamps();

            // Índices para mejorar el rendimiento de las consultas
            $table->index(['map_id', 'score']);
            $table->index(['user_id', 'map_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};

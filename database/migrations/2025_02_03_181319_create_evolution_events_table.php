<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evolution_events', function (Blueprint $table) {
            $table->id();
            $table->string('instance'); // Nome da instância
            $table->string('event'); // Tipo do evento
            $table->jsonb('data'); // JSON com os dados do evento
            $table->timestamps();
            $table->softDeletes(); // Adiciona coluna deleted_at para SoftDeletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evolution_events');
    }
};

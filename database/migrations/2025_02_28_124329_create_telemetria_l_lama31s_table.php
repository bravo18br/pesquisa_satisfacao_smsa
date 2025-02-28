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
        Schema::create('telemetria_l_lama31s', function (Blueprint $table) {
            $table->id();
            $table->string('embeddings');
            $table->string('temperature');
            $table->string('topP');
            $table->string('processamentoEmbeddings');
            $table->string('processamentoLLM');
            $table->text('respostaLLM');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetria_l_lama31s');
    }
};

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
        Schema::create('processada_pesquisas', function (Blueprint $table) {
            $table->id();
            $table->string('numeroWhats')->nullable();
            $table->string('autorizacaoLGPD')->nullable();
            $table->text('nomeUnidadeSaude')->nullable();
            $table->text('recepcaoUnidade')->nullable();
            $table->text('limpezaUnidade')->nullable();
            $table->text('medicoQualidade')->nullable();
            $table->text('exameQualidade')->nullable();
            $table->text('tempoAtendimento')->nullable();
            $table->text('comentarioLivre')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processada_pesquisas');
    }
};

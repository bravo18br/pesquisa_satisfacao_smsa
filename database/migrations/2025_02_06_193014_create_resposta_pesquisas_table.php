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
        Schema::create('resposta_pesquisas', function (Blueprint $table) {
            $table->id();
            $table->string('numeroWhats')->nullable();
            $table->text('autorizacaoLGPD')->nullable();
            $table->text('nomeUnidadeSaude')->nullable();
            $table->text('recepcaoUnidade')->nullable();
            $table->text('limpezaUnidade')->nullable();
            $table->text('exameQualidade')->nullable();
            $table->text('medicoQualidade')->nullable();
            $table->text('pontualidadeAtendimento')->nullable();
            $table->text('notaGeral')->nullable();
            $table->text('observacaoLivre')->nullable();
            $table->boolean('pesquisaConcluida')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resposta_pesquisas');
    }
};

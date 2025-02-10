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
            $table->boolean('autorizacaoLGPD');
            $table->string('nomeUnidadeSaude');
            $table->text('recepcaoUnidade');
            $table->text('limpezaUnidade');
            $table->text('exameQualidade');
            $table->text('medicoQualidade');
            $table->text('pontualidadeAtendimento');
            $table->text('notaGeral');
            $table->text('observacaoLivre');
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

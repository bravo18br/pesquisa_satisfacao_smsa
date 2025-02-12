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
            $table->string('autorizacaoLGPD');
            $table->text('nomeUnidadeSaude');
            $table->text('recepcaoUnidade');
            $table->text('limpezaUnidade');
            $table->text('medicoQualidade');
            $table->text('exameQualidade');
            $table->text('tempoAtendimento');
            $table->text('comentarioLivre');
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

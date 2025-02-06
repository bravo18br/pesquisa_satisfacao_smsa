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
        Schema::create('pesquisas', function (Blueprint $table) {
            $table->id();
            $table->string('telefone')->nullable();
            $table->string('unidade')->nullable();
            $table->string('status_pesquisa'); // job não iniciado, job iniciado, primeiro contato, aguardando, encerrada
            $table->tinyInteger('atendimento_recepcao')->nullable();
            $table->tinyInteger('ambiente_limpeza')->nullable();
            $table->string('pontualidade_exame')->nullable();
            $table->tinyInteger('realizacao_exame')->nullable();
            $table->tinyInteger('atendimento_medico')->nullable();
            $table->tinyInteger('avaliacao_geral')->nullable();
            $table->text('avaliacao_livre')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesquisas');
    }
};

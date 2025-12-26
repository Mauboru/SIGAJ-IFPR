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
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->morphs('arquivoable'); // Polymorphic: materia, aula, atividade, prova
            $table->string('nome_original');
            $table->string('caminho');
            $table->string('tipo'); // plano_ensino, material_aula, material_atividade, material_prova
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamanho')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arquivos');
    }
};

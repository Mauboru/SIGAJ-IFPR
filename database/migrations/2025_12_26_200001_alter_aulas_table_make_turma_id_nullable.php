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
        Schema::table('aulas', function (Blueprint $table) {
            // Remover constraint anterior se existir
            $table->dropForeign(['turma_id']);
        });
        
        Schema::table('aulas', function (Blueprint $table) {
            // Tornar turma_id nullable
            $table->unsignedBigInteger('turma_id')->nullable()->change();
        });
        
        Schema::table('aulas', function (Blueprint $table) {
            // Recriar foreign key
            $table->foreign('turma_id')->references('id')->on('turmas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aulas', function (Blueprint $table) {
            // Remover constraint
            $table->dropForeign(['turma_id']);
        });
        
        Schema::table('aulas', function (Blueprint $table) {
            // Tornar turma_id obrigatório novamente
            $table->unsignedBigInteger('turma_id')->nullable(false)->change();
        });
        
        Schema::table('aulas', function (Blueprint $table) {
            // Recriar foreign key
            $table->foreign('turma_id')->references('id')->on('turmas')->onDelete('cascade');
        });
    }
};


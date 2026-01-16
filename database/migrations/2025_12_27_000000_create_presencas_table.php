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
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('cascade');
            $table->foreignId('aluno_id')->constrained('users')->onDelete('cascade');
            $table->boolean('presente')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->unique(['aula_id', 'aluno_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\EpiController;
use App\Http\Controllers\FichaEpiController;
use App\Http\Controllers\AssinaturaEntregaEpiController;
use App\Http\Controllers\FuncoesController;
use App\Http\Controllers\SetorController;
use App\Http\Resources\UsuarioResource;
use App\Http\Controllers\RomaneioEntregaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\AtividadeController;
use App\Http\Controllers\ProvaController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\ArquivoController;

// Rotas públicas de autenticação
Route::group(['prefix' => 'auth'], function () {
  Route::post('login', [AuthController::class, 'login']);
  Route::post('register', [AuthController::class, 'register']);
});

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
  // Auth
  Route::post('auth/logout', [AuthController::class, 'logout']);
  Route::get('auth/me', [AuthController::class, 'me']);

  // Usuários
  Route::apiResource('usuarios', UsuarioController::class);

  // Matérias
  Route::apiResource('materias', MateriaController::class);

  // Turmas
  Route::apiResource('turmas', TurmaController::class);
  Route::post('turmas/{turma}/alunos', [TurmaController::class, 'adicionarAlunos']);
  Route::delete('turmas/{turma}/alunos/{aluno}', [TurmaController::class, 'removerAluno']);

  // Aulas
  Route::apiResource('aulas', AulaController::class);
  Route::post('aulas/{aula}/arquivos', [AulaController::class, 'uploadArquivo']);

  // Atividades
  Route::apiResource('atividades', AtividadeController::class);

  // Provas
  Route::apiResource('provas', ProvaController::class);

  // Notas
  Route::apiResource('notas', NotaController::class);
  Route::get('notas/turma/{turmaId}', [NotaController::class, 'porTurma']);
  Route::get('notas/aluno/{alunoId}', [NotaController::class, 'porAluno']);

  // Arquivos
  Route::get('arquivos/{arquivo}/download', [ArquivoController::class, 'download']);
  Route::delete('arquivos/{arquivo}', [ArquivoController::class, 'destroy']);
});

Route::prefix('ficha_epi')->group(function () {
  Route::post('create', [FichaEpiController::class, 'create']);
  Route::get('index', [FichaEpiController::class, 'index']);
  Route::get('show/{id}', [FichaEpiController::class, 'show']);
  Route::get('getAllFichas', [FichaEpiController::class, 'getAllFichas']);
});

Route::prefix('assinatura')->group(function () {
  Route::post('create', [AssinaturaEntregaEpiController::class, 'create']);
  Route::post('entrega-epi', [AssinaturaEntregaEpiController::class, 'assinarEntregaEpi']);
  Route::post('romaneio/create', [AssinaturaEntregaEpiController::class, 'createRomaneio']);
  Route::post('group/create', [AssinaturaEntregaEpiController::class, 'createGroup']);
  Route::put('update', [AssinaturaEntregaEpiController::class, 'update']);
});

Route::prefix('epi')->group(function () {
  Route::get('index', [EpiController::class, 'index']);
  Route::get('getAllEpis', [EpiController::class, 'getAllEpis']);
});

Route::prefix('funcionario')->group(function () {
  Route::get('index', [FuncionarioController::class, 'index']);
});

Route::prefix('funcoes')->group(function () {
  Route::get('index', [FuncoesController::class, 'index']);
});

Route::prefix('setor')->group(function () {
  Route::get('index', [SetorController::class, 'index']);
});

Route::prefix('romaneio')->group(function () {
  Route::get('index', [RomaneioEntregaController::class, 'index']);
  Route::get('show/{id}', [RomaneioEntregaController::class, 'show']);
  Route::get('details/{id}', [RomaneioEntregaController::class, 'details']);
});

// auqi precisa proteger a rota
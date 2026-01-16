<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Presenca;
use App\Models\Turma;
use Illuminate\Http\Request;
use App\Http\Requests\Aula\StoreAulaRequest;
use App\Http\Requests\Aula\UpdateAulaRequest;
use Illuminate\Support\Facades\Storage;

class AulaController extends Controller
{
    public function index(Request $request)
    {
        $query = Aula::with(['turma', 'materia', 'arquivos']);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        } else {
            // Aluno vê aulas de todas as turmas que está matriculado
            // Buscar IDs das turmas do aluno
            $turmaIds = Turma::whereHas('alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
            })->pluck('id');
            
            // Filtrar aulas por essas turmas
            if ($turmaIds->isNotEmpty()) {
                $query->whereIn('turma_id', $turmaIds);
            } else {
                // Se não tem turmas, não retorna nenhuma aula
                $query->whereRaw('1 = 0');
            }
        }

        $query->orderBy('data', 'desc');

        return response()->json($query->get());
    }

    public function store(StoreAulaRequest $request)
    {
        $aula = Aula::create($request->validated());

        return response()->json($aula->load(['turma', 'materia']), 201);
    }

    public function show(Request $request, Aula $aula)
    {
        // Carregar relacionamentos necessários para verificação
        $aula->load(['turma.alunos', 'materia']);
        
        // Verificar permissão básica
        if ($request->user()->isProfessor()) {
            if ($aula->turma->professor_id !== $request->user()->id) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }
        } else {
            // Aluno só pode ver se estiver matriculado na turma
            if (!$aula->turma->alunos->contains($request->user()->id)) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }
        }

        return response()->json($aula->load([
            'turma.alunos',
            'materia',
            'arquivos',
            'presencas.aluno'
        ]));
    }

    public function update(UpdateAulaRequest $request, Aula $aula)
    {
        $this->authorize('update', $aula);

        $aula->update($request->validated());

        return response()->json($aula->load(['turma', 'materia']));
    }

    public function destroy(Request $request, Aula $aula)
    {
        $this->authorize('delete', $aula);

        // Remove arquivos relacionados
        foreach ($aula->arquivos as $arquivo) {
            Storage::disk('public')->delete($arquivo->caminho);
            $arquivo->delete();
        }

        $aula->delete();

        return response()->json(['message' => 'Aula excluída com sucesso']);
    }

    public function uploadArquivo(Request $request, Aula $aula)
    {
        $this->authorize('update', $aula);

        $request->validate([
            'arquivo' => 'required|file|mimes:pdf|max:10240',
        ]);

        $arquivoFile = $request->file('arquivo');
        
        if (!$arquivoFile || !$arquivoFile->isValid()) {
            return response()->json(['message' => 'Arquivo inválido'], 422);
        }

        $caminho = $arquivoFile->store('aulas/' . $aula->id . '/arquivos', 'public');

        $arquivo = $aula->arquivos()->create([
            'nome_original' => $arquivoFile->getClientOriginalName(),
            'caminho' => $caminho,
            'tipo' => 'material_aula',
            'mime_type' => $arquivoFile->getMimeType(),
            'tamanho' => $arquivoFile->getSize(),
        ]);

        return response()->json($arquivo->load('arquivoable'), 201);
    }

    public function registrarChamada(Request $request, Aula $aula)
    {
        // Carregar turma para verificação
        $aula->load('turma');
        
        // Apenas professor pode registrar chamada
        if (!$request->user()->isProfessor() || $aula->turma->professor_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $request->validate([
            'presencas' => 'required|array',
            'presencas.*.aluno_id' => 'required|exists:users,id',
            'presencas.*.presente' => 'required|boolean',
            'presencas.*.observacoes' => 'nullable|string',
        ]);

        foreach ($request->presencas as $presencaData) {
            Presenca::updateOrCreate(
                [
                    'aula_id' => $aula->id,
                    'aluno_id' => $presencaData['aluno_id'],
                ],
                [
                    'presente' => $presencaData['presente'],
                    'observacoes' => $presencaData['observacoes'] ?? null,
                ]
            );
        }

        return response()->json([
            'message' => 'Chamada registrada com sucesso',
            'presencas' => $aula->presencas()->with('aluno')->get()
        ]);
    }

    public function obterChamada(Request $request, Aula $aula)
    {
        // Carregar relacionamentos necessários para verificação
        $aula->load('turma.alunos');
        
        // Verificar permissão
        if ($request->user()->isProfessor()) {
            if ($aula->turma->professor_id !== $request->user()->id) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }
        } else {
            if (!$aula->turma->alunos->contains($request->user()->id)) {
                return response()->json(['message' => 'Não autorizado'], 403);
            }
        }

        return response()->json($aula->presencas()->with('aluno')->get());
    }
}





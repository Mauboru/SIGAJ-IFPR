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
        // Carregar turma com alunos para verificação
        $aula->load('turma.alunos');
        
        // Apenas professor pode registrar chamada
        if (!$request->user()->isProfessor() || $aula->turma->professor_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $request->validate([
            'presencas' => 'required|array|min:1',
            'presencas.*.aluno_id' => 'required|integer|exists:users,id',
            'presencas.*.quantidade_faltas' => 'required|integer|min:0',
            'presencas.*.observacoes' => 'nullable|string|max:500',
        ]);

        // Verificar se os alunos pertencem à turma da aula
        $alunoIds = collect($request->presencas)->pluck('aluno_id')->unique();
        $alunosNaTurma = $aula->turma->alunos->pluck('id');
        
        $presencasSalvas = 0;
        foreach ($request->presencas as $presencaData) {
            $alunoId = (int) $presencaData['aluno_id'];
            
            // Verificar se o aluno pertence à turma
            if (!$alunosNaTurma->contains($alunoId)) {
                continue; // Pular alunos que não pertencem à turma
            }
            
            $quantidadeFaltas = isset($presencaData['quantidade_faltas']) 
                ? (int) $presencaData['quantidade_faltas'] 
                : 0;
            
            // Garantir que quantidade_faltas não seja maior que quantidade_aulas da aula
            $quantidadeFaltas = min($quantidadeFaltas, $aula->quantidade_aulas ?? 1);
            
            Presenca::updateOrCreate(
                [
                    'aula_id' => $aula->id,
                    'aluno_id' => $alunoId,
                ],
                [
                    'quantidade_faltas' => $quantidadeFaltas,
                    'presente' => $quantidadeFaltas === 0, // Presente se não tiver faltas
                    'observacoes' => !empty($presencaData['observacoes']) ? $presencaData['observacoes'] : null,
                ]
            );
            $presencasSalvas++;
        }

        if ($presencasSalvas === 0) {
            return response()->json([
                'message' => 'Nenhuma presença válida foi salva. Verifique se os alunos pertencem à turma.',
            ], 422);
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

    public function estatisticasPresenca(Request $request)
    {
        // Apenas alunos podem ver suas próprias estatísticas
        if ($request->user()->isProfessor()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $alunoId = $request->user()->id;

        // Buscar todas as aulas das turmas do aluno
        $turmaIds = \App\Models\Turma::whereHas('alunos', function ($q) use ($alunoId) {
            $q->where('aluno_id', $alunoId);
        })->pluck('id');

        $aulas = Aula::whereIn('turma_id', $turmaIds)
            ->with(['presencas' => function ($q) use ($alunoId) {
                $q->where('aluno_id', $alunoId);
            }, 'materia', 'turma'])
            ->orderBy('data', 'asc')
            ->get();

        $totalAulas = 0;
        $totalFaltas = 0;
        $presencasDetalhadas = [];

        foreach ($aulas as $aula) {
            $quantidadeAulas = $aula->quantidade_aulas ?? 1;
            $totalAulas += $quantidadeAulas;

            $presenca = $aula->presencas->first();
            $quantidadeFaltas = $presenca ? ($presenca->quantidade_faltas ?? 0) : 0;
            $totalFaltas += $quantidadeFaltas;

            $presencasDetalhadas[] = [
                'aula_id' => $aula->id,
                'titulo' => $aula->titulo,
                'data' => $aula->data,
                'materia' => $aula->materia->nome ?? 'N/A',
                'turma' => $aula->turma->nome ?? 'N/A',
                'quantidade_aulas' => $quantidadeAulas,
                'quantidade_faltas' => $quantidadeFaltas,
                'quantidade_presencas' => $quantidadeAulas - $quantidadeFaltas,
                'observacoes' => $presenca->observacoes ?? null,
            ];
        }

        $totalPresencas = $totalAulas - $totalFaltas;
        $percentualPresenca = $totalAulas > 0 ? round(($totalPresencas / $totalAulas) * 100, 2) : 0;
        $percentualFalta = $totalAulas > 0 ? round(($totalFaltas / $totalAulas) * 100, 2) : 0;

        return response()->json([
            'total_aulas' => $totalAulas,
            'total_presencas' => $totalPresencas,
            'total_faltas' => $totalFaltas,
            'percentual_presenca' => $percentualPresenca,
            'percentual_falta' => $percentualFalta,
            'presencas_detalhadas' => $presencasDetalhadas,
        ]);
    }
}





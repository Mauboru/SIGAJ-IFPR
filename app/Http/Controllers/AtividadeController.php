<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use App\Models\Turma;
use Illuminate\Http\Request;
use App\Http\Requests\Atividade\StoreAtividadeRequest;
use App\Http\Requests\Atividade\UpdateAtividadeRequest;

class AtividadeController extends Controller
{
    public function index(Request $request)
    {
        $query = Atividade::with(['turma', 'materia', 'aula']);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        } else {
            // Aluno vê atividades de turmas que está matriculado
            $query->whereHas('turma.alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
            });
        }

        $query->orderBy('data_entrega', 'desc');

        return response()->json($query->get());
    }

    public function store(StoreAtividadeRequest $request)
    {
        $data = $request->validated();
        $atividade = Atividade::create($data);

        // Atribuir automaticamente aos alunos da turma (criar notas vazias)
        $turma = Turma::with('alunos')->find($data['turma_id']);
        foreach ($turma->alunos as $aluno) {
            $atividade->notas()->create([
                'aluno_id' => $aluno->id,
                'turma_id' => $turma->id,
                'materia_id' => $data['materia_id'],
                'atividade_id' => $atividade->id,
                'valor' => 0,
            ]);
        }

        return response()->json($atividade->load(['turma', 'materia', 'aula']), 201);
    }

    public function show(Request $request, Atividade $atividade)
    {
        $this->authorize('view', $atividade);

        return response()->json($atividade->load(['turma', 'materia', 'aula', 'notas.aluno']));
    }

    public function update(UpdateAtividadeRequest $request, Atividade $atividade)
    {
        $this->authorize('update', $atividade);

        $atividade->update($request->validated());

        return response()->json($atividade->load(['turma', 'materia', 'aula']));
    }

    public function destroy(Request $request, Atividade $atividade)
    {
        $this->authorize('delete', $atividade);

        $atividade->delete();

        return response()->json(['message' => 'Atividade excluída com sucesso']);
    }
}


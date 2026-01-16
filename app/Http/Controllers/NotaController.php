<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use Illuminate\Http\Request;
use App\Http\Requests\Nota\StoreNotaRequest;
use App\Http\Requests\Nota\UpdateNotaRequest;

class NotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Nota::with(['aluno', 'turma', 'materia', 'atividade', 'prova']);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        } else {
            // Aluno vê apenas suas notas do semestre atual
            $semestreAtual = $this->getSemestreAtual();
            $anoAtual = date('Y');
            
            $query->where('aluno_id', $request->user()->id)
                ->whereHas('turma', function ($q) use ($semestreAtual, $anoAtual) {
                    $q->where('semestre', $semestreAtual)
                      ->where('ano', $anoAtual);
                });
        }

        return response()->json($query->get());
    }

    /**
     * Retorna o semestre atual baseado na data
     * Semestre 1: Janeiro a Junho
     * Semestre 2: Julho a Dezembro
     */
    private function getSemestreAtual()
    {
        $mesAtual = (int) date('m');
        return $mesAtual >= 1 && $mesAtual <= 6 ? 1 : 2;
    }

    public function store(StoreNotaRequest $request)
    {
        $nota = Nota::create($request->validated());

        return response()->json($nota->load(['aluno', 'turma', 'materia', 'atividade', 'prova']), 201);
    }

    public function show(Request $request, Nota $nota)
    {
        $this->authorize('view', $nota);

        return response()->json($nota->load(['aluno', 'turma', 'materia', 'atividade', 'prova']));
    }

    public function update(UpdateNotaRequest $request, Nota $nota)
    {
        $this->authorize('update', $nota);

        $nota->update($request->validated());

        return response()->json($nota->load(['aluno', 'turma', 'materia', 'atividade', 'prova']));
    }

    public function destroy(Request $request, Nota $nota)
    {
        $this->authorize('delete', $nota);

        $nota->delete();

        return response()->json(['message' => 'Nota excluída com sucesso']);
    }

    public function porTurma(Request $request, $turmaId)
    {
        $query = Nota::with(['aluno', 'materia', 'atividade', 'prova'])
            ->where('turma_id', $turmaId);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        } else {
            $query->where('aluno_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    public function porAluno(Request $request, $alunoId)
    {
        if ($request->user()->isAluno() && $request->user()->id != $alunoId) {
            abort(403, 'Você só pode ver suas próprias notas');
        }

        $query = Nota::with(['turma', 'materia', 'atividade', 'prova'])
            ->where('aluno_id', $alunoId);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        }

        return response()->json($query->get());
    }
}





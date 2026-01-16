<?php

namespace App\Http\Controllers;

use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Turma\StoreTurmaRequest;
use App\Http\Requests\Turma\UpdateTurmaRequest;

class TurmaController extends Controller
{
    public function index(Request $request)
    {
        $query = Turma::with(['professor', 'materias', 'alunos']);

        if ($request->user()->isProfessor()) {
            $query->where('professor_id', $request->user()->id);
        } else {
            // Aluno vê todas as turmas que está matriculado
            $query->whereHas('alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
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

    public function store(StoreTurmaRequest $request)
    {
        $data = $request->validated();
        $data['professor_id'] = $request->user()->id;

        $turma = Turma::create($data);

        return response()->json($turma->load(['professor', 'materias']), 201);
    }

    public function show(Request $request, Turma $turma)
    {
        $this->authorize('view', $turma);

        return response()->json($turma->load(['professor', 'materias', 'alunos']));
    }

    public function update(UpdateTurmaRequest $request, Turma $turma)
    {
        $this->authorize('update', $turma);

        $turma->update($request->validated());

        return response()->json($turma->load(['professor', 'materias']));
    }

    public function destroy(Request $request, Turma $turma)
    {
        $this->authorize('delete', $turma);

        $turma->delete();

        return response()->json(['message' => 'Turma excluída com sucesso']);
    }

    public function adicionarAlunos(Request $request, Turma $turma)
    {
        $this->authorize('update', $turma);

        $request->validate([
            'aluno_ids' => 'required|array',
            'aluno_ids.*' => 'exists:users,id',
        ]);

        // Verificar se são alunos
        $alunos = User::whereIn('id', $request->aluno_ids)
            ->where('role', 'aluno')
            ->get();

        if ($alunos->count() !== count($request->aluno_ids)) {
            return response()->json(['message' => 'Alguns usuários não são alunos'], 422);
        }

        $turma->alunos()->syncWithoutDetaching($alunos->pluck('id'));

        return response()->json($turma->load('alunos'));
    }

    public function removerAluno(Request $request, Turma $turma, User $aluno)
    {
        $this->authorize('update', $turma);

        $turma->alunos()->detach($aluno->id);

        return response()->json(['message' => 'Aluno removido da turma com sucesso']);
    }
}





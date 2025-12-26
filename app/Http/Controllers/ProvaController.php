<?php

namespace App\Http\Controllers;

use App\Models\Prova;
use App\Models\Turma;
use Illuminate\Http\Request;
use App\Http\Requests\Prova\StoreProvaRequest;
use App\Http\Requests\Prova\UpdateProvaRequest;

class ProvaController extends Controller
{
    public function index(Request $request)
    {
        $query = Prova::with(['turma', 'materia']);

        if ($request->user()->isProfessor()) {
            $query->whereHas('turma', function ($q) use ($request) {
                $q->where('professor_id', $request->user()->id);
            });
        } else {
            // Aluno vê provas de turmas que está matriculado
            $query->whereHas('turma.alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
            });
        }

        $query->orderBy('data', 'desc');

        return response()->json($query->get());
    }

    public function store(StoreProvaRequest $request)
    {
        $data = $request->validated();
        $prova = Prova::create($data);

        // Atribuir automaticamente aos alunos da turma (criar notas vazias)
        $turma = Turma::with('alunos')->find($data['turma_id']);
        foreach ($turma->alunos as $aluno) {
            $prova->notas()->create([
                'aluno_id' => $aluno->id,
                'turma_id' => $turma->id,
                'materia_id' => $data['materia_id'],
                'prova_id' => $prova->id,
                'valor' => 0,
            ]);
        }

        return response()->json($prova->load(['turma', 'materia']), 201);
    }

    public function show(Request $request, Prova $prova)
    {
        $this->authorize('view', $prova);

        return response()->json($prova->load(['turma', 'materia', 'notas.aluno']));
    }

    public function update(UpdateProvaRequest $request, Prova $prova)
    {
        $this->authorize('update', $prova);

        $prova->update($request->validated());

        return response()->json($prova->load(['turma', 'materia']));
    }

    public function destroy(Request $request, Prova $prova)
    {
        $this->authorize('delete', $prova);

        $prova->delete();

        return response()->json(['message' => 'Prova excluída com sucesso']);
    }
}


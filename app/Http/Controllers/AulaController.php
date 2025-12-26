<?php

namespace App\Http\Controllers;

use App\Models\Aula;
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
            // Aluno vê aulas de turmas que está matriculado
            $query->whereHas('turma.alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
            });
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
        $this->authorize('view', $aula);

        return response()->json($aula->load(['turma', 'materia', 'arquivos']));
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

        $caminho = $request->file('arquivo')->store('aulas/' . $aula->id . '/arquivos', 'public');

        $arquivo = $aula->arquivos()->create([
            'nome_original' => $request->file('arquivo')->getClientOriginalName(),
            'caminho' => $caminho,
            'tipo' => 'material_aula',
        ]);

        return response()->json($arquivo, 201);
    }
}


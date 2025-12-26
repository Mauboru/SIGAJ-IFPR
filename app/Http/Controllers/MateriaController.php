<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use App\Http\Requests\Materia\StoreMateriaRequest;
use App\Http\Requests\Materia\UpdateMateriaRequest;
use Illuminate\Support\Facades\Storage;

class MateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Materia::with('professor');

        if ($request->user()->isProfessor()) {
            $query->where('professor_id', $request->user()->id);
        } else {
            // Aluno vê matérias de turmas que está matriculado
            $query->whereHas('turmas.alunos', function ($q) use ($request) {
                $q->where('aluno_id', $request->user()->id);
            });
        }

        return response()->json($query->get());
    }

    public function store(StoreMateriaRequest $request)
    {
        $data = $request->except(['plano_ensino']);
        $data['professor_id'] = $request->user()->id;

        $materia = Materia::create($data);

        // Upload do plano de ensino se fornecido
        if ($request->hasFile('plano_ensino')) {
            $path = $request->file('plano_ensino')->store('materias/' . $materia->id . '/plano_ensino', 'public');
            $materia->update(['plano_ensino_path' => $path]);

            // Criar registro de arquivo
            $materia->arquivos()->create([
                'nome_original' => $request->file('plano_ensino')->getClientOriginalName(),
                'caminho' => $path,
                'tipo' => 'plano_ensino',
                'mime_type' => $request->file('plano_ensino')->getMimeType(),
                'tamanho' => $request->file('plano_ensino')->getSize(),
            ]);
        }

        return response()->json($materia->load(['professor', 'arquivos']), 201);
    }

    public function show(Request $request, Materia $materia)
    {
        $this->authorize('view', $materia);

        return response()->json($materia->load(['professor', 'turmas', 'arquivos']));
    }

    public function update(UpdateMateriaRequest $request, Materia $materia)
    {
        $this->authorize('update', $materia);

        $data = $request->except(['plano_ensino']);
        $materia->update($data);

        // Upload do plano de ensino se fornecido
        if ($request->hasFile('plano_ensino')) {
            // Remover arquivo antigo se existir
            if ($materia->plano_ensino_path) {
                Storage::disk('public')->delete($materia->plano_ensino_path);
                $materia->arquivos()->where('tipo', 'plano_ensino')->delete();
            }

            $path = $request->file('plano_ensino')->store('materias/' . $materia->id . '/plano_ensino', 'public');
            $materia->update(['plano_ensino_path' => $path]);

            // Criar registro de arquivo
            $materia->arquivos()->create([
                'nome_original' => $request->file('plano_ensino')->getClientOriginalName(),
                'caminho' => $path,
                'tipo' => 'plano_ensino',
                'mime_type' => $request->file('plano_ensino')->getMimeType(),
                'tamanho' => $request->file('plano_ensino')->getSize(),
            ]);
        }

        return response()->json($materia->load(['professor', 'arquivos']));
    }

    public function destroy(Request $request, Materia $materia)
    {
        $this->authorize('delete', $materia);

        // Remover arquivos
        foreach ($materia->arquivos as $arquivo) {
            Storage::disk('public')->delete($arquivo->caminho);
        }

        $materia->delete();

        return response()->json(['message' => 'Matéria excluída com sucesso']);
    }
}

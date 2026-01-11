<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Aula;
use App\Models\Turma;
use Illuminate\Http\Request;
use App\Http\Requests\Materia\StoreMateriaRequest;
use App\Http\Requests\Materia\UpdateMateriaRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Materia::with(['professor', 'aulas', 'turmas']);

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
        return DB::transaction(function () use ($request) {
            $data = $request->except(['aulas', 'turma_ids', 'arquivos_extras']);
            $data['professor_id'] = $request->user()->id;

            $materia = Materia::create($data);

            // Vincular múltiplas turmas se fornecidas
            $turmaIds = $request->input('turma_ids');
            // Se vier como string JSON, decodificar
            if (is_string($turmaIds)) {
                $turmaIds = json_decode($turmaIds, true);
            }
            
            if ($turmaIds && is_array($turmaIds)) {
                // Converter strings para inteiros se necessário
                $turmaIds = array_map('intval', $turmaIds);
                
                // Validar que as turmas pertencem ao professor
                $turmasValidas = Turma::whereIn('id', $turmaIds)
                    ->where('professor_id', $request->user()->id)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($turmasValidas)) {
                    $materia->turmas()->sync($turmasValidas);
                }
            }

            // Criar aulas do plano de ensino se fornecidas
            $aulas = $request->input('aulas');
            if ($aulas) {
                // Se for string JSON, decodificar
                if (is_string($aulas)) {
                    $aulas = json_decode($aulas, true);
                }
                
                if (is_array($aulas)) {
                    foreach ($aulas as $aulaData) {
                        if (!empty($aulaData['titulo']) && !empty($aulaData['data'])) {
                            Aula::create([
                                'materia_id' => $materia->id,
                                'titulo' => $aulaData['titulo'],
                                'data' => $aulaData['data'],
                            ]);
                        }
                    }
                }
            }

            // Upload de arquivos extras (PDFs) se fornecidos
            if ($request->hasFile('arquivos_extras')) {
                foreach ($request->file('arquivos_extras') as $arquivo) {
                    $path = $arquivo->store('materias/' . $materia->id . '/arquivos_extras', 'public');
                    
                    $materia->arquivos()->create([
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'caminho' => $path,
                        'tipo' => 'arquivo_extra',
                        'mime_type' => $arquivo->getMimeType(),
                        'tamanho' => $arquivo->getSize(),
                    ]);
                }
            }

            return response()->json($materia->load(['professor', 'turmas', 'aulas', 'arquivos']), 201);
        });
    }

    public function show(Request $request, Materia $materia)
    {
        $this->authorize('view', $materia);

        return response()->json($materia->load(['professor', 'turmas', 'aulas', 'arquivos']));
    }

    public function update(UpdateMateriaRequest $request, Materia $materia)
    {
        $this->authorize('update', $materia);

        $data = $request->except(['aulas', 'turma_ids', 'arquivos_extras']);
        $materia->update($data);

        // Vincular múltiplas turmas se fornecidas
        $turmaIds = $request->input('turma_ids');
        if (is_string($turmaIds)) {
            $turmaIds = json_decode($turmaIds, true);
        }
        
        if ($turmaIds !== null && is_array($turmaIds)) {
            $turmaIds = array_map('intval', $turmaIds);
            $turmasValidas = Turma::whereIn('id', $turmaIds)
                ->where('professor_id', $request->user()->id)
                ->pluck('id')
                ->toArray();
            
            if (!empty($turmasValidas)) {
                $materia->turmas()->sync($turmasValidas);
            } elseif (empty($turmaIds)) {
                $materia->turmas()->detach();
            }
        }

        // Atualizar aulas se fornecidas
        if ($request->has('aulas')) {
            $aulas = $request->input('aulas');
            if (is_string($aulas)) {
                $aulas = json_decode($aulas, true);
            }
            
            if (is_array($aulas)) {
                $materia->aulas()->delete();
                foreach ($aulas as $aulaData) {
                    if (!empty($aulaData['titulo']) && !empty($aulaData['data'])) {
                        Aula::create([
                            'materia_id' => $materia->id,
                            'titulo' => $aulaData['titulo'],
                            'data' => $aulaData['data'],
                        ]);
                    }
                }
            }
        }

        // Upload de arquivos extras (PDFs) se fornecidos
        if ($request->hasFile('arquivos_extras')) {
            foreach ($request->file('arquivos_extras') as $arquivo) {
                $path = $arquivo->store('materias/' . $materia->id . '/arquivos_extras', 'public');
                
                $materia->arquivos()->create([
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'caminho' => $path,
                    'tipo' => 'arquivo_extra',
                    'mime_type' => $arquivo->getMimeType(),
                    'tamanho' => $arquivo->getSize(),
                ]);
            }
        }

        return response()->json($materia->load(['professor', 'turmas', 'aulas', 'arquivos']));
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

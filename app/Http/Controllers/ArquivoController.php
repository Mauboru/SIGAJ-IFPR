<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArquivoController extends Controller
{
    public function download(Request $request, Arquivo $arquivo)
    {
        // Verificar permissão baseado no relacionamento
        $user = $request->user();

        if ($arquivo->arquivoable_type === 'App\\Models\\Materia') {
            $materia = $arquivo->arquivoable;
            if ($user->isProfessor() && $materia->professor_id !== $user->id) {
                abort(403);
            }
            if ($user->isAluno() && !$user->turmasComoAluno()->where('materia_id', $materia->id)->exists()) {
                abort(403);
            }
        } elseif ($arquivo->arquivoable_type === 'App\\Models\\Aula') {
            $aula = $arquivo->arquivoable;
            if ($user->isProfessor() && $aula->turma->professor_id !== $user->id) {
                abort(403);
            }
            if ($user->isAluno() && !$aula->turma->alunos()->where('aluno_id', $user->id)->exists()) {
                abort(403);
            }
        }

        if (!Storage::disk('public')->exists($arquivo->caminho)) {
            abort(404);
        }

        return Storage::disk('public')->download($arquivo->caminho, $arquivo->nome_original);
    }

    public function destroy(Request $request, Arquivo $arquivo)
    {
        $user = $request->user();

        // Verificar permissão
        if ($arquivo->arquivoable_type === 'App\\Models\\Materia') {
            $materia = $arquivo->arquivoable;
            if ($user->isProfessor() && $materia->professor_id !== $user->id) {
                abort(403);
            }
        } elseif ($arquivo->arquivoable_type === 'App\\Models\\Aula') {
            $aula = $arquivo->arquivoable;
            if ($user->isProfessor() && $aula->turma->professor_id !== $user->id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        Storage::disk('public')->delete($arquivo->caminho);
        $arquivo->delete();

        return response()->json(['message' => 'Arquivo excluído com sucesso']);
    }
}



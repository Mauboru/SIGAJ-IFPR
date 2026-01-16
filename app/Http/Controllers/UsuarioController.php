<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        // Apenas professores podem ver lista de usuários
        if (!$request->user()->isProfessor()) {
            abort(403, 'Apenas professores podem visualizar usuários');
        }

        $query = User::query();

        // Filtro por role se fornecido
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->with('turmasComoAluno')->get());
    }

    public function store(StoreUsuarioRequest $request)
    {
        // Apenas professores podem criar usuários
        if (!$request->user()->isProfessor()) {
            abort(403, 'Apenas professores podem criar usuários');
        }

        $data = $request->validated();
        $turmaIds = $data['turma_ids'] ?? [];
        unset($data['turma_ids']);
        
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Upload de foto se fornecido
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('usuarios/' . $user->id . '/fotos', 'public');
            $user->update(['foto' => $path]);
        }

        // Vincular aluno a turmas se for aluno e turmas foram fornecidas
        if ($user->isAluno() && !empty($turmaIds)) {
            $turmasValidas = Turma::whereIn('id', $turmaIds)
                ->where('professor_id', $request->user()->id)
                ->pluck('id')
                ->toArray();
            
            if (!empty($turmasValidas)) {
                $user->turmasComoAluno()->sync($turmasValidas);
            }
        }

        return response()->json($user->load('turmasComoAluno'), 201);
    }

    public function show(Request $request, User $usuario)
    {
        // Usuário pode ver seu próprio perfil ou professor pode ver qualquer usuário
        if ($request->user()->id !== $usuario->id && !$request->user()->isProfessor()) {
            abort(403, 'Você não tem permissão para visualizar este usuário');
        }

        return response()->json($usuario->load('turmasComoAluno'));
    }

    public function update(UpdateUsuarioRequest $request, User $usuario)
    {
        // Usuário pode atualizar seu próprio perfil ou professor pode atualizar qualquer usuário
        if ($request->user()->id !== $usuario->id && !$request->user()->isProfessor()) {
            abort(403, 'Você não tem permissão para atualizar este usuário');
        }

        $data = $request->validated();
        $turmaIds = $data['turma_ids'] ?? null;
        unset($data['turma_ids']);

        // Atualizar senha se fornecida
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Remover foto antiga se nova foto for fornecida
        if ($request->hasFile('foto')) {
            if ($usuario->foto) {
                Storage::disk('public')->delete($usuario->foto);
            }
            $path = $request->file('foto')->store('usuarios/' . $usuario->id . '/fotos', 'public');
            $data['foto'] = $path;
        }

        $usuario->update($data);

        // Atualizar vínculos de turmas se for aluno e turmas foram fornecidas
        if ($usuario->isAluno() && $turmaIds !== null) {
            $turmasValidas = Turma::whereIn('id', $turmaIds)
                ->where('professor_id', $request->user()->id)
                ->pluck('id')
                ->toArray();
            
            $usuario->turmasComoAluno()->sync($turmasValidas);
        }

        return response()->json($usuario->load('turmasComoAluno'));
    }

    public function destroy(Request $request, User $usuario)
    {
        // Apenas professores podem excluir usuários
        if (!$request->user()->isProfessor()) {
            abort(403, 'Apenas professores podem excluir usuários');
        }

        // Não permitir excluir a si mesmo
        if ($request->user()->id === $usuario->id) {
            abort(403, 'Você não pode excluir seu próprio usuário');
        }

        // Remover foto se existir
        if ($usuario->foto) {
            Storage::disk('public')->delete($usuario->foto);
        }

        $usuario->delete();

        return response()->json(['message' => 'Usuário excluído com sucesso']);
    }
}

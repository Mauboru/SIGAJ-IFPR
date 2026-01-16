<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'instituicao' => $user->instituicao,
                'foto' => $user->foto,
            ],
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:professor,aluno',
            'instituicao' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'instituicao' => $request->instituicao,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'instituicao' => $user->instituicao,
                'foto' => $user->foto,
            ],
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'instituicao' => $user->instituicao,
            'foto' => $user->foto,
        ];

        // Se for aluno, incluir informações das turmas do semestre atual
        if ($user->isAluno()) {
            $semestreAtual = $this->getSemestreAtual();
            $anoAtual = date('Y');
            
            $turmasSemestreAtual = $user->turmasComoAluno()
                ->where('semestre', $semestreAtual)
                ->where('ano', $anoAtual)
                ->with(['materias', 'professor'])
                ->get();
            
            $data['turmas_semestre_atual'] = $turmasSemestreAtual;
            $data['semestre_atual'] = $semestreAtual;
            $data['ano_atual'] = $anoAtual;
        }

        return response()->json($data);
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
}

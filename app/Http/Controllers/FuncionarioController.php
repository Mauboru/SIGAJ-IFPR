<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcionario;

class FuncionarioController extends Controller {
    public function index(Request $request) {
        try {
            $funcionarios = Funcionario::query()
                ->where('ATIVO', 1)
                ->leftJoin('funcoes as fu', 'fu.CODIGO', '=', 'funcionarios.COD_FUNCAO')
                ->leftJoin('departamentos as d', 'd.CODIGO', '=', 'funcionarios.COD_DEPARTAMENTO')
                ->select(
                    'funcionarios.CODIGO',
                    'funcionarios.NOME',
                    'funcionarios.N_CARTEIRA',
                    'funcionarios.DATA_ADMISSAO',
                    'funcionarios.COD_DEPARTAMENTO',
                    'funcionarios.COD_FUNCAO',
                    'd.DEPARTAMENTO as DEPARTAMENTO',
                    'fu.FUNCAO as FUNCAO'
                )
                ->orderBy('funcionarios.NOME', 'asc')
                ->get();

            return response()->json($funcionarios);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro desconhecido. Tente novamente mais tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
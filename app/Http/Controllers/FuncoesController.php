<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Funcoes;

class FuncoesController extends Controller {
    public function index(Request $request) {
        try {
            $funcoes = Funcoes::orderBy('FUNCAO', 'asc')->get();
            return response()->json($funcoes);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro desconhecido. Tente novamente mais tarde.', 'error' => $e->getMessage()], 500);
        }
    }
}
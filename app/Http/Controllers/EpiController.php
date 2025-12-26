<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BaixaProdutos;
use App\Models\ProdBaixaProduto;
use App\Models\Epi;

class EpiController extends Controller {
    public function index(Request $request) {
        try {
            $codFuncionario = $request->input('COD_FUNCIONARIO');
            $dtInicial = $request->input('DATA_INICIAL');
            $dtFinal = $request->input('DATA_FINAL');

            if (!$codFuncionario || !$dtInicial || !$dtFinal) return response()->json(['message' => 'Código do funcionário e período são obrigatórios.'], 400);

            $codBaixaProduto = BaixaProdutos::where('COD_FUNCIONAR_RECEBE', $codFuncionario)->pluck('CODIGO');
            $baixas = ProdBaixaProduto::whereIn('COD_BAIXA_PRODUTO', $codBaixaProduto)
                ->where('COD_CATEGORIA', '75') //CODIGO DE EPIS E EPCS
                ->whereBetween('DATA_ENTREGA', [$dtInicial, $dtFinal])
                ->orderBy('CODIGO', 'asc')
                ->get();

            if ($baixas->isEmpty()) return response()->json(['message' => 'Nenhum registro encontrado para o período informado.'], 404);

            return response()->json($baixas);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro desconhecido. Tente novamente mais tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllEpis() {
        try {
            $epis = Epi::all()->count();
            if (!$epis) return response()->json(['message' => 'Não foi encontrado nenhum EPI'], 400);
            return response($epis);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro desconhecido. Tente novamente mais tarde.', 'error' => $e->getMessage()], 500);
        }
    }
}
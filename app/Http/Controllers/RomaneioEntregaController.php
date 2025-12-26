<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BaixaProdutos;
use App\Models\ProdBaixaProduto;
use App\Models\Projeto;

class RomaneioEntregaController extends Controller {
    public function index(Request $request) {
        try {
            $codFuncionario = $request->input('COD_FUNCIONARIO');
            $dtInicial = $request->input('DATA_INICIAL');
            $dtFinal = $request->input('DATA_FINAL');

            $query = BaixaProdutos::query();

            if (!empty($codFuncionario)) {
                $query->where('COD_FUNCIONAR_RECEBE', $codFuncionario);
            }

            if (!empty($dtInicial) && !empty($dtFinal)) {
                $query->whereBetween('DATA_ENTREGA', [$dtInicial, $dtFinal]);
            }

            $perPage = 50;
            $page = $request->input('page', 1);
            
            $paginatedRomaneios = $query->with(['funcionarioRecebe', 'funcionarioEntrega', 'centroCusto', 'assinatura'])
                ->orderBy('CODIGO', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $romaneios = $paginatedRomaneios->getCollection()->map(function ($romaneio) {
                $itens = ProdBaixaProduto::with(['projeto'])
                    ->where('COD_BAIXA_PRODUTO', $romaneio->CODIGO)
                    ->get();

                $totalItens = $itens->count();
                
                // Verifica se o romaneio está assinado (uma única assinatura por romaneio)
                $assinado = $romaneio->assinatura && !empty($romaneio->assinatura->ASSINATURA);

                // Busca COD_PROJETO da tabela prod_baixa_produtos e retorna o NOME_PROJETO
                $itemComProjeto = $itens->first(function ($item) {
                    return isset($item->COD_PROJETO) 
                        && !empty($item->COD_PROJETO)
                        && $item->projeto;
                });
                
                $nomeProjeto = $itemComProjeto?->projeto?->NOME_PROJETO;

                return [
                    'CODIGO' => $romaneio->CODIGO,
                    'DATA_ENTREGA' => $romaneio->DATA_ENTREGA ?? null,
                    'ASSINADO' => $assinado,
                    'TOTAL_ITENS' => $totalItens,
                    'TOTAL_ASSINADOS' => $assinado ? $totalItens : 0,
                    'NUM_BAIXA' => $romaneio->NUM_BAIXA,
                    'NOME_FUNCIONARIO_RECEBE' => $romaneio->funcionarioRecebe->NOME ?? null,
                    'NUM_PROJETO' => $nomeProjeto ?? null,
                    'NOME_FUNCIONARIO_ENTREGA' => $romaneio->funcionarioEntrega->NOME ?? null,
                    'COD_CENTRO_CUSTO' => $romaneio->COD_CENTRO_CUSTO ?? null,
                    'CENTRO_CUSTO' => $romaneio->centroCusto?->CENTRO_CUSTO ?? null,
                ];
            });

            return response()->json([
                'data' => $romaneios,
                'current_page' => $paginatedRomaneios->currentPage(),
                'per_page' => $paginatedRomaneios->perPage(),
                'total' => $paginatedRomaneios->total(),
                'last_page' => $paginatedRomaneios->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro desconhecido. Tente novamente mais tarde.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id) {
        try {
            $romaneio = BaixaProdutos::with(['funcionarioRecebe', 'funcionarioEntrega', 'centroCusto'])->find($id);
            
            if (!$romaneio) {
                return response()->json(['message' => 'Romaneio não encontrado.'], 404);
            }

            $itens = ProdBaixaProduto::with(['produto', 'assinatura'])
                ->where('COD_BAIXA_PRODUTO', $romaneio->CODIGO)
                ->get();

            return response()->json($itens);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar itens do romaneio.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function details($id) {
        try {
            $baixa = BaixaProdutos::with(['funcionarioRecebe', 'funcionarioEntrega', 'centroCusto'])->find($id);
            
            if (!$baixa) {
                return response()->json(['message' => 'Baixa não encontrada.'], 404);
            }

            $produtosCollection = ProdBaixaProduto::with(['produto', 'baixa', 'projeto'])
                ->where('COD_BAIXA_PRODUTO', $baixa->CODIGO)
                ->get();

            // Busca COD_PROJETO da tabela prod_baixa_produtos e retorna o NOME_PROJETO
            $itemComProjeto = $produtosCollection->first(function ($item) {
                return isset($item->COD_PROJETO) 
                    && !empty($item->COD_PROJETO)
                    && $item->projeto;
            });
            
            $nomeProjeto = $itemComProjeto?->projeto?->NOME_PROJETO;

            // Verifica se o romaneio está assinado (uma única assinatura por romaneio)
            $baixa->load('assinatura');
            $assinado = $baixa->assinatura && !empty($baixa->assinatura->ASSINATURA);
            $assinaturaUrl = $baixa->assinatura->ASSINATURA ?? null;

            $produtos = $produtosCollection->map(function ($item) use ($assinado, $assinaturaUrl) {
                return [
                    'CODIGO' => $item->CODIGO,
                    'DESCRICAO' => $item->produto->DESCRICAO ?? $item->DESCRICAO ?? null,
                    'QUANTIDADE' => $item->QUANTIDADE ?? 0,
                    'PRECO' => $item->PRECO ?? 0,
                    'SUBTOTAL' => $item->SUBTOTAL ?? 0,
                    'DATA_ENTREGA' => $item->DATA_ENTREGA ?? null,
                    'DATA_DEVOLUCAO' => $item->DATA_DEVOLUCAO ?? null,
                    'ASSINADO' => $assinado,
                    'ASSINATURA_URL' => $assinaturaUrl,
                    'NUM_BAIXA' => $item->baixa->NUM_BAIXA ?? null,
                    'NUM_PROJETO' => $item->baixa->NUM_PROJETO ?? null,
                ];
            });

            return response()->json([
                'baixa' => [
                    'CODIGO' => $baixa->CODIGO,
                    'NUM_BAIXA' => $baixa->NUM_BAIXA ?? null,
                    'DATA_ENTREGA' => $baixa->DATA_ENTREGA ?? null,
                    'OBS' => $baixa->OBS ?? $baixa->OBSERVACOES ?? null,
                    'NUM_PROJETO' => $nomeProjeto ?? $baixa->NUM_PROJETO ?? null,
                    'COD_PROJETO' => $baixa->COD_PROJETO ?? null,
                    'COD_CENTRO_CUSTO' => $baixa->COD_CENTRO_CUSTO ?? null,
                    'CENTRO_CUSTO' => $baixa->centroCusto?->CENTRO_CUSTO ?? null,
                    'FUNCIONARIO_RECEBE' => $baixa->funcionarioRecebe ? [
                        'CODIGO' => $baixa->funcionarioRecebe->CODIGO ?? null,
                        'NOME' => $baixa->funcionarioRecebe->NOME ?? null,
                    ] : null,
                    'FUNCIONARIO_ENTREGA' => $baixa->funcionarioEntrega ? [
                        'CODIGO' => $baixa->funcionarioEntrega->CODIGO ?? null,
                        'NOME' => $baixa->funcionarioEntrega->NOME ?? null,
                    ] : null,
                    'COD_FUNCIONARIO_ENTREGA' => $baixa->COD_FUNCIONARIO_ENTREGA ?? null,
                ],
                'produtos' => $produtos,
                'assinatura_url' => $assinaturaUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar detalhes da baixa.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
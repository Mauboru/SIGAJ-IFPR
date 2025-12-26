<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FichaEpi;
use App\Models\EntregaFichaEpi;
use App\Models\AssinaturaEntregaEpi;
use App\Models\AssinaturaFichaEpi;
use App\Models\EntregaEpi;
use App\Models\Epi;
use App\Models\Produto;
use App\Models\BaixaProdutos;
use App\Models\ProdBaixaProduto;
use App\Http\Controllers\Controller;

class FichaEpiController extends Controller {
    public function create(Request $request) {
        try {
            $request->validate([
                'colaboradorId' => 'required|integer',
                'funcionarioId' => 'required|integer',
                'dataEmissao' => 'required|date',
                'dataAdmissao' => 'nullable|date',
                'matricula' => 'nullable|string',
                'funcao' => 'nullable|integer',
                'setor' => 'nullable|integer',
                'observacoes' => 'nullable|string',
                'periodoEntrega' => 'required|array',
                'periodoEntrega.dtInicial' => 'required|date',
                'periodoEntrega.dtFinal' => 'required|date',
            ]);

            $ficha = FichaEpi::create([
                'DATA_CADASTRO' => $request->dataEmissao,
                'DATA_ADMISSAO' => $request->dataAdmissao,
                'DATA_EMISSAO' => $request->dataEmissao,
                'DATA_ALTERACAO' => $request->dataEmissao,
                'DATA_INICIAL_ENTREGA' => $request->periodoEntrega['dtInicial'],
                'DATA_FINAL_ENTREGA' => $request->periodoEntrega['dtFinal'],
                'COD_FUNCIONARIO_RESP' => $request->funcionarioId,
                'COD_FUNCIONARIO' => $request->colaboradorId,
                'COD_FUNCAO' => $request->funcao,
                'COD_SETOR' => $request->setor,
                'MATRICULA' => $request->matricula,
                'OBS' => $request->observacoes,
            ]);

            $entregas = EntregaEpi::whereBetween('DATA_ENTREGA', [
                $ficha->DATA_INICIAL_ENTREGA, 
                $ficha->DATA_FINAL_ENTREGA
            ])->get();  

            foreach ($entregas as $entrega) {
                EntregaFichaEpi::create([
                    'COD_FICHA_ENTREGA'   => $ficha->CODIGO,
                    'COD_ENTREGA_EPI'     => $entrega->COD_ENTREGA_EPI,
                    'COD_PROD_ENTREGA_EPI'=> $entrega->CODIGO,
                    'QUANTIDADE'          => $entrega->QUANTIDADE,
                    'DESCRICAO_EPI'       => $entrega->DESCRICAO_EPI,
                    'COD_EPI'             => $entrega->COD_EPI,
                    'COD_PROJETO'         => $entrega->COD_PROJETO,
                    'VALOR'               => $entrega->VALOR,
                    'SUBTOTAL'            => $entrega->SUBTOTAL,
                    'DATA_ENTREGA'        => $entrega->DATA_ENTREGA,
                    'DATA_VENCIMENTO'     => $entrega->DATA_VENCIMENTO
                ]);
            }

            return response()->json(['message' => 'Ficha de EPIs criada com sucesso!' ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro desconhecido. Tente novamente mais tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request) {
        try {
            $query = FichaEpi::with([
                'funcionario.funcao',
                'funcionario.departamento',
                'funcao',
                'departamento'
            ]);

            if ($request->filled('funcionario')) {
                $query->where('COD_FUNCIONARIO', $request->funcionario);
            }
            if ($request->filled('funcao')) {
                $query->where('COD_FUNCAO', $request->funcao);
            }
            if ($request->filled('setor')) {
                $query->where('COD_SETOR', $request->setor);
            }
            if ($request->filled('data')) {
                $query->whereDate('DATA_EMISSAO', $request->data);
            } 

            $fichas = $query->get();

            foreach ($fichas as $ficha) {
                // Verificar se a ficha inteira está assinada
                $assinaturaFicha = AssinaturaFichaEpi::where('COD_FICHA_EPI', $ficha->CODIGO)->first();
                
                // Buscar itens da ficha através de ProdBaixaProduto
                $codBaixaProduto = BaixaProdutos::where('COD_FUNCIONAR_RECEBE', $ficha->COD_FUNCIONARIO)->pluck('CODIGO');
                $produtosFicha = ProdBaixaProduto::whereIn('COD_BAIXA_PRODUTO', $codBaixaProduto)
                    ->where('COD_CATEGORIA', '75')
                    ->whereBetween('DATA_ENTREGA', [$ficha->DATA_INICIAL_ENTREGA, $ficha->DATA_FINAL_ENTREGA])
                    ->get();
                
                $totalEntregas = $produtosFicha->count();
                $entregasAssinadas = 0;
                
                foreach ($produtosFicha as $produto) {
                    // Buscar assinatura usando CODIGO do ProdBaixaProduto (item específico)
                    // COD_ENTREGA_PROD_EPI armazena o CODIGO do ProdBaixaProduto
                    $assinaturaItem = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $produto->CODIGO)->first();
                    if ($assinaturaItem) {
                        // Verificar se tem assinatura própria ou referência à assinatura da ficha
                        if (!empty($assinaturaItem->ASSINATURA) || $assinaturaItem->COD_ASSINATURA_FICHA) {
                            $entregasAssinadas++;
                        }
                    }
                }

                $status = 0;
                if ($totalEntregas > 0) {
                    if ($assinaturaFicha || ($entregasAssinadas == $totalEntregas && $entregasAssinadas > 0)) {
                        // Ficha inteira assinada ou todos os itens assinados
                        $status = 2;
                    } elseif ($entregasAssinadas == 0) {
                        $status = -1;
                    } elseif ($entregasAssinadas < $totalEntregas) {
                        $status = 1;
                    }
                }
                $ficha->status_assinatura = $status;
            }
            return response()->json($fichas);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro desconhecido. Tente novamente mais tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id) {
        try {
            $ficha = FichaEpi::with([
                'funcionario.funcao',
                'funcionario.departamento',
                'funcao',
                'departamento'
            ])->find($id);

            if (!$ficha) return response()->json(['message' => 'Ficha não encontrada.'], 404);

            // Buscar EPIs através de ProdBaixaProduto (compatibilidade com frontend)
            $codBaixaProduto = BaixaProdutos::where('COD_FUNCIONAR_RECEBE', $ficha->COD_FUNCIONARIO)->pluck('CODIGO');
            $epis = ProdBaixaProduto::with(['produto'])
                ->whereIn('COD_BAIXA_PRODUTO', $codBaixaProduto)
                ->where('COD_CATEGORIA', '75') //CODIGO DE EPIS E EPCS
                ->whereBetween('DATA_ENTREGA', [$ficha->DATA_INICIAL_ENTREGA, $ficha->DATA_FINAL_ENTREGA])
                ->get()
                ->map(function ($epi) use ($ficha) {
                    // Buscar assinatura do item em assinatura_prod_entrega_epi usando CODIGO do ProdBaixaProduto (item específico)
                    // COD_ENTREGA_PROD_EPI armazena o CODIGO do ProdBaixaProduto
                    $assinaturaItem = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $epi->CODIGO)->first();
                    
                    // Se o item tem referência à assinatura da ficha, buscar a assinatura da ficha
                    $assinaturaUrl = null;
                    if ($assinaturaItem) {
                        if ($assinaturaItem->COD_ASSINATURA_FICHA) {
                            // Item assinado através da ficha inteira, buscar assinatura da ficha
                            $assinaturaFicha = AssinaturaFichaEpi::find($assinaturaItem->COD_ASSINATURA_FICHA);
                            $assinaturaUrl = $assinaturaFicha ? $assinaturaFicha->ASSINATURA : null;
                        } else {
                            // Item assinado individualmente
                            $assinaturaUrl = $assinaturaItem->ASSINATURA;
                        }
                    }
                    
                    return [
                        'CODIGO' => $epi->CODIGO, // Manter CODIGO do ProdBaixaProduto para compatibilidade
                        'NOME' => $epi->produto->DESCRICAO ?? null,
                        'NUM_CA' => $epi->produto->NUM_CA ?? null,
                        'ASSINADO' => $assinaturaUrl,
                        'DATA_ENTREGA' => $epi->DATA_ENTREGA,
                        'QTD' => $epi->QUANTIDADE,
                        'DATA_DEVOLUCAO' => $epi->DATA_DEVOLUCAO,
                        'VALOR' => $epi->PRECO,
                        'SUBTOTAL' => $epi->SUBTOTAL,
                        'DATA_VENCIMENTO' => $epi->produto->DATA_VENCIMENTO ?? null,
                    ];
                });

            return response()->json([
                'ficha' => $ficha,
                'epis' => $epis
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro desconhecido. Tente novamente mais tarde.', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllFichas() {
        try {
            $fichas = FichaEpi::all()->count();
            if (!$fichas) return response()->json(['message' => 'Não foi encontrado nenhum EPI'], 400);
            return response($fichas);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro desconhecido. Tente novamente mais tarde.', 'error' => $e->getMessage()], 500);
        }
    }
}
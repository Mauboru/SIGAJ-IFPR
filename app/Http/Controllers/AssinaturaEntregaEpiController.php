<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssinaturaBaixaProdutos;
use App\Models\AssinaturaEntregaEpi;
use App\Models\AssinaturaFichaEpi;
use App\Models\BaixaProdutos;
use App\Models\FichaEpi;
use App\Models\ProdBaixaProduto;
use App\Models\EntregaEpi;
use App\Models\EntregaFichaEpi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AssinaturaEntregaEpiController extends Controller {
    public function create(Request $request) {
        $request->validate([
            'codigos' => 'required|array',
            'codigos.*' => 'integer',       
            'assinatura' => 'required|string',
            'id_ficha' => 'nullable|integer',
        ]);

        try {
            $dataUrl = $request->input('assinatura');

            if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) throw new \Exception('Base64 inválido.');
            } else {
                throw new \Exception('Formato da imagem inválido.');
            }

            // Converter imagem para JPG ou BMP (não mais PNG)
            $image = imagecreatefromstring($data);
            if ($image === false) throw new \Exception('Erro ao criar imagem a partir dos dados.');

            $width = imagesx($image);
            $height = imagesy($image);
            $tempFile = null;
            $ext = 'jpg';
            
            // Tentar salvar como JPG primeiro
            $jpgImage = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $image, 0, 0, 0, 0, $width, $height);
            
            $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.jpg';
            $saved = imagejpeg($jpgImage, $tempFile, 90);
            imagedestroy($jpgImage);
            
            if (!$saved) {
                // Se JPG falhar, tentar BMP
                $ext = 'bmp';
                $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.bmp';
                $saved = imagebmp($image, $tempFile);
                
                if (!$saved) {
                    imagedestroy($image);
                    throw new \Exception('Erro ao salvar imagem como JPG ou BMP.');
                }
            }
            
            imagedestroy($image);
            $data = file_get_contents($tempFile);
            unlink($tempFile);
            
            $filename = 'assinaturas/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $data);
            $urlCompleta = asset(Storage::url($filename));
            $hex = bin2hex($data);

            foreach ($request->codigos as $codigo) {
                $prodBaixaProduto = ProdBaixaProduto::find($codigo);
                if (!$prodBaixaProduto) continue;
                
                // Se houver id_ficha, salvar APENAS na tabela assinatura_prod_entrega_epi
                if ($request->filled('id_ficha')) {
                    $ficha = FichaEpi::find($request->id_ficha);
                    if (!$ficha) {
                        throw new \Exception('Ficha não encontrada. ID: ' . $request->id_ficha);
                    }

                    // Buscar o EntregaFichaEpi relacionado à ficha e ao produto
                    // Primeiro tentar por COD_EPI (sem data, pois pode haver diferença de formato)
                    $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                        ->where('COD_EPI', $prodBaixaProduto->COD_PRODUTO)
                        ->first();

                    // Se não encontrou, tentar por DESCRICAO_EPI
                    if (!$entregaFichaEpi && $prodBaixaProduto->DESCRICAO_PRODUTO) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->where('DESCRICAO_EPI', $prodBaixaProduto->DESCRICAO_PRODUTO)
                            ->first();
                    }

                    // Se ainda não encontrou, tentar por quantidade
                    if (!$entregaFichaEpi) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->where('QUANTIDADE', $prodBaixaProduto->QUANTIDADE)
                            ->first();
                    }

                    // Se ainda não encontrou, buscar qualquer item da ficha
                    if (!$entregaFichaEpi) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->first();
                    }

                    if (!$entregaFichaEpi) {
                        // Buscar quantos itens existem na ficha para debug
                        $totalItensFicha = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)->count();
                        $itensFicha = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)->pluck('COD_EPI')->toArray();
                        throw new \Exception('Item da ficha não encontrado. Ficha: ' . $ficha->CODIGO . ', Produto buscado: ' . $prodBaixaProduto->COD_PRODUTO . ', Total de itens na ficha: ' . $totalItensFicha . ', COD_EPIs na ficha: ' . implode(', ', $itensFicha));
                    }

                    if (!$entregaFichaEpi->COD_PROD_ENTREGA_EPI) {
                        throw new \Exception('COD_PROD_ENTREGA_EPI está vazio no registro encontrado. EntregaFichaEpi ID: ' . $entregaFichaEpi->CODIGO);
                    }

                    // Verificar se já existe assinatura para este COD_ENTREGA_PROD_EPI
                    $assinaturaExistente = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $entregaFichaEpi->COD_PROD_ENTREGA_EPI)->first();
                    
                    if ($assinaturaExistente) {
                        // Atualizar assinatura existente
                        $assinaturaExistente->ASSINATURA = $urlCompleta;
                        $assinaturaExistente->ASSINATURA_HEX = $hex;
                        $assinaturaExistente->ASSINATURA_BLOB = $data;
                        $assinaturaExistente->save();
                    } else {
                        // Criar nova assinatura
                        AssinaturaEntregaEpi::insert([
                            'COD_ENTREGA_PROD_EPI' => $entregaFichaEpi->COD_PROD_ENTREGA_EPI,
                            'ASSINATURA' => $urlCompleta,
                            'ASSINATURA_HEX' => $hex,
                            'ASSINATURA_BLOB' => $data,
                        ]);
                    }
                } else {
                    // Se não houver id_ficha (romaneio/baixa de produtos), salvar na tabela assinatura_baixa_produtos
                    AssinaturaBaixaProdutos::insert([
                        'ASSINATURA' => $urlCompleta,
                        'ASSINATURA_HEX' => $hex,
                        'ASSINATURA_BLOB' => $data,
                        'COD_BAIXA_PRODUTOS' => $prodBaixaProduto->COD_BAIXA_PRODUTO
                    ]);
                }
            }

            return response()->json(['message' => 'Assinaturas aplicadas com sucesso!', 'url' => $urlCompleta], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar assinatura.', 'error' => $e->getMessage()], 500);
        }
    }

    public function createGroup(Request $request) {
        $request->validate([
            'ids_fichas' => 'required|array',
            'ids_fichas.*' => 'integer',
            'assinatura' => 'required|string',
        ]);

        try {
            $dataUrl = $request->input('assinatura');

            if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) throw new \Exception('Base64 inválido.');
            } else {
                throw new \Exception('Formato da imagem inválido.');
            }

            // Converter imagem para JPG ou BMP (não mais PNG)
            $image = imagecreatefromstring($data);
            if ($image === false) throw new \Exception('Erro ao criar imagem a partir dos dados.');

            $width = imagesx($image);
            $height = imagesy($image);
            $tempFile = null;
            $ext = 'jpg';
            
            // Tentar salvar como JPG primeiro
            $jpgImage = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $image, 0, 0, 0, 0, $width, $height);
            
            $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.jpg';
            $saved = imagejpeg($jpgImage, $tempFile, 90);
            imagedestroy($jpgImage);
            
            if (!$saved) {
                // Se JPG falhar, tentar BMP
                $ext = 'bmp';
                $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.bmp';
                $saved = imagebmp($image, $tempFile);
                
                if (!$saved) {
                    imagedestroy($image);
                    throw new \Exception('Erro ao salvar imagem como JPG ou BMP.');
                }
            }
            
            imagedestroy($image);
            $data = file_get_contents($tempFile);
            unlink($tempFile);
            
            $filename = 'assinaturas/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $data);
            $urlCompleta = asset(Storage::url($filename));
            $hex = bin2hex($data);

            foreach ($request->ids_fichas as $idFicha) {
                $ficha = FichaEpi::find($idFicha);
                if (!$ficha) continue;

                // Salvar assinatura da ficha inteira na tabela assinatura_ficha_epi
                $assinaturaFichaExistente = AssinaturaFichaEpi::where('COD_FICHA_EPI', $ficha->CODIGO)->first();
                
                if ($assinaturaFichaExistente) {
                    // Atualizar assinatura existente da ficha
                    $assinaturaFichaExistente->ASSINATURA = $urlCompleta;
                    $assinaturaFichaExistente->ASSINATURA_HEX = $hex;
                    $assinaturaFichaExistente->ASSINATURA_BLOB = $data;
                    $assinaturaFichaExistente->save();
                    $codAssinaturaFicha = $assinaturaFichaExistente->CODIGO;
                } else {
                    // Criar nova assinatura da ficha
                    $novaAssinaturaFicha = AssinaturaFichaEpi::create([
                        'COD_FICHA_EPI' => $ficha->CODIGO,
                        'ASSINATURA' => $urlCompleta,
                        'ASSINATURA_HEX' => $hex,
                        'ASSINATURA_BLOB' => $data,
                    ]);
                    $codAssinaturaFicha = $novaAssinaturaFicha->CODIGO;
                }

                // Buscar todos os itens da ficha através de ProdBaixaProduto
                $codBaixaProduto = BaixaProdutos::where('COD_FUNCIONAR_RECEBE', $ficha->COD_FUNCIONARIO)->pluck('CODIGO');
                $produtosFicha = ProdBaixaProduto::whereIn('COD_BAIXA_PRODUTO', $codBaixaProduto)
                    ->where('COD_CATEGORIA', '75')
                    ->whereBetween('DATA_ENTREGA', [$ficha->DATA_INICIAL_ENTREGA, $ficha->DATA_FINAL_ENTREGA])
                    ->get();

                // Criar/atualizar assinaturas dos itens referenciando a assinatura da ficha
                foreach ($produtosFicha as $produto) {
                    $assinaturaItemExistente = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $produto->CODIGO)->first();
                    
                    if ($assinaturaItemExistente) {
                        // Atualizar assinatura existente do item com referência à ficha
                        $assinaturaItemExistente->COD_ASSINATURA_FICHA = $codAssinaturaFicha;
                        $assinaturaItemExistente->save();
                    } else {
                        // Criar nova assinatura do item apenas com referência à ficha
                        AssinaturaEntregaEpi::insert([
                            'COD_ENTREGA_PROD_EPI' => $produto->CODIGO, // CODIGO único do item
                            'COD_ASSINATURA_FICHA' => $codAssinaturaFicha, // Referência à assinatura da ficha
                            'COD_BAIXA_PRODUTO' => $produto->COD_BAIXA_PRODUTO,
                        ]);
                    }
                }
            }

            return response()->json([
                'message' => 'Assinaturas aplicadas com sucesso!',
                'url' => $urlCompleta
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao salvar assinatura.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function createRomaneio(Request $request) {
        $request->validate([
            'codigo_romaneio' => 'required|integer',
            'assinatura' => 'required|string',
        ]);

        try {
            $dataUrl = $request->input('assinatura');

            if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) throw new \Exception('Base64 inválido.');
            } else {
                throw new \Exception('Formato da imagem inválido.');
            }

            // Converter imagem para JPG ou BMP (não mais PNG)
            $image = imagecreatefromstring($data);
            if ($image === false) throw new \Exception('Erro ao criar imagem a partir dos dados.');

            $width = imagesx($image);
            $height = imagesy($image);
            $tempFile = null;
            $ext = 'jpg';
            
            // Tentar salvar como JPG primeiro
            $jpgImage = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $image, 0, 0, 0, 0, $width, $height);
            
            $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.jpg';
            $saved = imagejpeg($jpgImage, $tempFile, 90);
            imagedestroy($jpgImage);
            
            if (!$saved) {
                // Se JPG falhar, tentar BMP
                $ext = 'bmp';
                $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.bmp';
                $saved = imagebmp($image, $tempFile);
                
                if (!$saved) {
                    imagedestroy($image);
                    throw new \Exception('Erro ao salvar imagem como JPG ou BMP.');
                }
            }
            
            imagedestroy($image);
            $data = file_get_contents($tempFile);
            unlink($tempFile);
            
            $filename = 'assinaturas/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $data);
            $urlCompleta = asset(Storage::url($filename));
            $hex = bin2hex($data);

            // Verificar se já existe assinatura para este romaneio
            $baixa = BaixaProdutos::find($request->codigo_romaneio);
            if (!$baixa) {
                throw new \Exception('Romaneio não encontrado.');
            }

            // Verificar se já existe assinatura para este COD_BAIXA_PRODUTO
            $assinaturaExistente = AssinaturaBaixaProdutos::where('COD_BAIXA_PRODUTOS', $request->codigo_romaneio)->first();
            
            if ($assinaturaExistente) {
                // Atualizar assinatura existente
                $assinaturaExistente->ASSINATURA = $urlCompleta;
                $assinaturaExistente->ASSINATURA_HEX = $hex;
                $assinaturaExistente->ASSINATURA_BLOB = $data;
                $assinaturaExistente->save();
            } else {
                // Criar nova assinatura (apenas uma por romaneio)
                AssinaturaBaixaProdutos::insert([
                    'ASSINATURA' => $urlCompleta,
                    'ASSINATURA_HEX' => $hex,
                    'ASSINATURA_BLOB' => $data,
                    'COD_BAIXA_PRODUTOS' => $request->codigo_romaneio
                ]);
            }

            return response()->json(['message' => 'Romaneio assinado com sucesso!', 'url' => $urlCompleta], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar assinatura.', 'error' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request) {
        try {
            $request->validate([
                'codigo' => 'required|integer',
                'data'   => 'required|date',
            ]);

            $assinatura = ProdBaixaProduto::where('CODIGO', $request->codigo)->first();
            $assinatura->DATA_DEVOLUCAO = $request->data;
            $assinatura->save();

            return response()->json(['message' => 'Data atualizada com sucesso!'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao salvar assinatura.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    //EPI - assinatura_entrega_epi
    public function assinarEntregaEpi(Request $request) {
        $request->validate([
            'codigos' => 'required|array',
            'codigos.*' => 'integer',       
            'assinatura' => 'required|string',
            'id_ficha' => 'nullable|integer',
        ]);

        try {
            $dataUrl = $request->input('assinatura');

            if (preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) throw new \Exception('Base64 inválido.');
            } else {
                throw new \Exception('Formato da imagem inválido.');
            }

            $image = imagecreatefromstring($data);
            if ($image === false) throw new \Exception('Erro ao criar imagem a partir dos dados.');

            $width = imagesx($image);
            $height = imagesy($image);
            $tempFile = null;
            $ext = 'jpg';
            
            $jpgImage = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($jpgImage, 255, 255, 255);
            imagefill($jpgImage, 0, 0, $white);
            imagecopy($jpgImage, $image, 0, 0, 0, 0, $width, $height);
            
            $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.jpg';
            $saved = imagejpeg($jpgImage, $tempFile, 90);
            imagedestroy($jpgImage);
            
            if (!$saved) {
                $ext = 'bmp';
                $tempFile = sys_get_temp_dir() . '/' . Str::uuid() . '.bmp';
                $saved = imagebmp($image, $tempFile);
                
                if (!$saved) {
                    imagedestroy($image);
                    throw new \Exception('Erro ao salvar imagem como JPG ou BMP.');
                }
            }
            
            imagedestroy($image);
            $data = file_get_contents($tempFile);
            unlink($tempFile);
            
            $filename = 'assinaturas/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $data);
            $urlCompleta = asset(Storage::url($filename));
            $hex = bin2hex($data);

            foreach ($request->codigos as $codigo) {
                $prodBaixaProduto = ProdBaixaProduto::find($codigo);
                if (!$prodBaixaProduto) continue;
                
                if ($request->filled('id_ficha')) {
                    $ficha = FichaEpi::find($request->id_ficha);
                    if (!$ficha) {
                        throw new \Exception('Ficha não encontrada. ID: ' . $request->id_ficha);
                    }

                    $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                        ->where('COD_EPI', $prodBaixaProduto->COD_PRODUTO)
                        ->first();

                    if (!$entregaFichaEpi && $prodBaixaProduto->DESCRICAO_PRODUTO) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->where('DESCRICAO_EPI', $prodBaixaProduto->DESCRICAO_PRODUTO)
                            ->first();
                    }

                    if (!$entregaFichaEpi) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->where('QUANTIDADE', $prodBaixaProduto->QUANTIDADE)
                            ->first();
                    }

                    if (!$entregaFichaEpi) {
                        $entregaFichaEpi = EntregaFichaEpi::where('COD_FICHA_ENTREGA', $ficha->CODIGO)
                            ->first();
                    }

                    // Verificar se já existe assinatura para este CODIGO do ProdBaixaProduto (item específico)
                    // Usar COD_ENTREGA_PROD_EPI para armazenar o CODIGO do ProdBaixaProduto
                    $assinaturaExistente = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $prodBaixaProduto->CODIGO)->first();
                    
                    if ($assinaturaExistente) {
                        $assinaturaExistente->ASSINATURA = $urlCompleta;
                        $assinaturaExistente->ASSINATURA_HEX = $hex;
                        $assinaturaExistente->ASSINATURA_BLOB = $data;
                        $assinaturaExistente->save();
                    } else {
                        AssinaturaEntregaEpi::insert([
                            'COD_ENTREGA_PROD_EPI' => $prodBaixaProduto->CODIGO, // CODIGO único do item
                            'COD_BAIXA_PRODUTO' => $prodBaixaProduto->COD_BAIXA_PRODUTO,
                            'ASSINATURA' => $urlCompleta,
                            'ASSINATURA_HEX' => $hex,
                            'ASSINATURA_BLOB' => $data,
                        ]);
                    }
                } else {
                    // Se não houver id_ficha, salvar usando CODIGO do ProdBaixaProduto
                    $assinaturaExistente = AssinaturaEntregaEpi::where('COD_ENTREGA_PROD_EPI', $prodBaixaProduto->CODIGO)->first();
                    
                    if ($assinaturaExistente) {
                        $assinaturaExistente->ASSINATURA = $urlCompleta;
                        $assinaturaExistente->ASSINATURA_HEX = $hex;
                        $assinaturaExistente->ASSINATURA_BLOB = $data;
                        $assinaturaExistente->save();
                    } else {
                        AssinaturaEntregaEpi::insert([
                            'COD_ENTREGA_PROD_EPI' => $prodBaixaProduto->CODIGO, // CODIGO único do item
                            'COD_BAIXA_PRODUTO' => $prodBaixaProduto->COD_BAIXA_PRODUTO,
                            'ASSINATURA' => $urlCompleta,
                            'ASSINATURA_HEX' => $hex,
                            'ASSINATURA_BLOB' => $data,
                        ]);
                    }
                }
            }

            return response()->json(['message' => 'Assinaturas aplicadas com sucesso!', 'url' => $urlCompleta], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao salvar assinatura.', 'error' => $e->getMessage()], 500);
        }
    }
}
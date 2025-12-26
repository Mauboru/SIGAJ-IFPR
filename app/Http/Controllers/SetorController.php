<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setor;

class SetorController extends Controller {
    public function index(Request $request) {
        try {
            $setor = Setor::orderBy('DEPARTAMENTO', 'asc')->get();
            return response()->json($setor);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro desconhecido. Tente novamente mais tarde.', 'error' => $e->getMessage()], 500);
        }
    }
}
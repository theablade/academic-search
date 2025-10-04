<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtigoRequest;
use App\Services\ElasticsearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log as FacadesLog;
use Log;

class ArticleController extends Controller
{
    protected $elasticsearch;

    public function __construct(ElasticsearchService $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Display a listing of the resource.
     */
   public function index()
{
    try {
        $artigos = $this->elasticsearch->searchDocuments('artigos');
        return response()->json($artigos);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao buscar artigos',
            'details' => $e->getMessage(),
        ], 500);
    }
}




public function search(string $query)
{
    try {
        $resultados = $this->elasticsearch->searchDocuments('artigos', $query);
        return response()->json($resultados);
    } catch (\Exception $e) {
        FacadesLog::error('Erro na busca: ' . $e->getMessage(), [
            'query' => $query,
            'stack' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'error' => 'Erro ao buscar artigos',
            'details' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreArtigoRequest $request): JsonResponse
    {
        $doc = $request->only(['titulo', 'autor', 'ano', 'resumo', 'conteudo']);

        try {
            $response = $this->elasticsearch->indexDocument('artigos', $doc);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao criar artigo', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtigoRequest;
use App\Services\ElasticsearchService;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    protected $elasticsearch;
    protected $geminiService;

    public function __construct(ElasticsearchService $elasticsearch, GeminiService $geminiService)
    {
        $this->elasticsearch = $elasticsearch;
        $this->geminiService = $geminiService;
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
            Log::error('Erro ao buscar artigos: ' . $e->getMessage());
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
            Log::error('Erro na busca: ' . $e->getMessage(), [
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
     * Store a newly created resource in storage.
     */
    public function store(StoreArtigoRequest $request): JsonResponse
    {
        $doc = $request->only(['titulo', 'autor', 'ano', 'resumo', 'conteudo']);

        try {
            $response = $this->elasticsearch->indexDocument('artigos', $doc);
            return response()->json($response, 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar artigo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao criar artigo',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'pergunta' => 'required|string|max:1000'
        ]);

        try {
            $pergunta = $request->input('pergunta');

            // Busca artigos relevantes
            $artigos = $this->elasticsearch->searchDocuments('artigos', $pergunta);

            // Gera resposta com IA
            $resposta = $this->geminiService->generateChatResponse($pergunta, $artigos);

            // Formata artigos para resposta
            $artigosFormatados = array_map(function($artigo) {
                $source = $artigo['_source'] ?? $artigo;
                return [
                    'titulo' => $source['titulo'] ?? '',
                    'autor' => $source['autor'] ?? '',
                    'ano' => $source['ano'] ?? ''
                ];
            }, array_slice($artigos, 0, 3));

            return response()->json([
                'resposta' => $resposta,
                'artigos' => $artigosFormatados,
                'artigos_encontrados' => count($artigos)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro no chat: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erro ao gerar resposta',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function summarize(Request $request): JsonResponse
    {
        // Log da requisição recebida
        Log::info('Requisição de resumo recebida:', $request->all());

        $request->validate([
            'doc' => 'required_without:id|array',
            'doc.titulo' => 'required_with:doc|string',
            'doc.autor' => 'required_with:doc|string',
            'doc.ano' => 'required_with:doc',
            'doc.resumo' => 'nullable|string',
            'doc.conteudo' => 'required_with:doc|string',
            'id' => 'required_without:doc|string',
            'lang' => 'nullable|string|in:Português,English,Español'
        ]);

        try {
            $doc = $request->input('doc');
            $id = $request->input('id');
            $lang = $request->input('lang', 'Português');

            // Se não tem doc mas tem ID, busca no Elasticsearch
            if (!$doc && $id) {
                Log::info('Buscando artigo por ID: ' . $id);
                $result = $this->elasticsearch->getDocument('artigos', $id);
                $doc = $result['_source'] ?? null;

                if (!$doc) {
                    Log::error('Artigo não encontrado: ' . $id);
                    return response()->json(['error' => 'Artigo não encontrado'], 404);
                }
            }

            // Valida se tem conteúdo
            if (empty($doc['conteudo'])) {
                Log::error('Artigo sem conteúdo', ['doc' => $doc]);
                return response()->json([
                    'error' => 'Artigo não possui conteúdo para resumir',
                    'details' => 'O campo conteudo está vazio'
                ], 400);
            }

            Log::info('Gerando resumo para: ' . ($doc['titulo'] ?? 'Sem título'));

            // Gera o resumo
            $summary = $this->geminiService->summarizeArticle($doc, $lang);

            Log::info('Resumo gerado com sucesso');

            return response()->json([
                'summary' => $summary,
                'meta' => [
                    'titulo' => $doc['titulo'] ?? '',
                    'autor' => $doc['autor'] ?? '',
                    'ano' => $doc['ano'] ?? ''
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Dados inválidos',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao resumir artigo:', [
                'message' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'error' => 'Falha ao gerar resumo',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $artigo = $this->elasticsearch->getDocument('artigos', $id);
            return response()->json($artigo);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar artigo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Artigo não encontrado',
                'details' => $e->getMessage()
            ], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->elasticsearch->deleteDocument('artigos', $id);
            return response()->json(['message' => 'Artigo deletado com sucesso'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar artigo: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao deletar artigo',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

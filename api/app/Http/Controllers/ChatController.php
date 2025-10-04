<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Elastic\Elasticsearch\ClientBuilder;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        try {
            $query = $request->input('pergunta');

            if (empty($query)) {
                return response()->json(['error' => 'Campo "pergunta" é obrigatório'], 400);
            }

            // Conecta no Elasticsearch
            $es = ClientBuilder::create()
                ->setHosts([env('ELASTICSEARCH_HOST')])
                ->setApiKey(env('ELASTIC_API_KEY'))
                ->build();

            // Busca híbrida: texto + semântica
            $searchBody = [
                'size' => 3,
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'multi_match' => [
                                    'query'  => $query,
                                    'fields' => ['titulo^3', 'resumo^2'],
                                    'type'   => 'best_fields'
                                ]
                            ],
                            [
                                'semantic' => [
                                    'field' => 'conteudo',
                                    'query' => $query
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $search = $es->search([
                'index' => 'artigos',
                'body'  => $searchBody
            ]);

            // Filtra hits válidos
            $hits = array_filter($search['hits']['hits'] ?? [], fn($h) => isset($h['_source']));
            $hits = array_map(fn($h) => $h['_source'], $hits);

            if (empty($hits)) {
                return response()->json(['resposta' => 'Nenhum artigo encontrado sobre esse tema.']);
            }

            // Monta contexto seguro
            $context = collect($hits)->map(function ($doc) {
                return "Título: " . ($doc['titulo'] ?? 'N/A') . "\n" .
                       "Autor: " . ($doc['autor'] ?? 'N/A') . "\n" .
                       "Ano: " . ($doc['ano'] ?? 'N/A') . "\n" .
                       "Resumo: " . ($doc['resumo'] ?? 'N/A') . "\n" .
                       "Conteúdo: " . ($doc['conteudo'] ?? 'N/A');
            })->implode("\n\n---\n\n");

            // Conecta no Gemini via API
            $http = new Client(['verify' => false]);
            $geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=" . env('GEMINI_API_KEY');

            $prompt = "Você é um assistente acadêmico especializado. Baseado nos seguintes artigos científicos:\n\n$context\n\n---\n\nPergunta do usuário: $query\n\nInstruções:\n- Responda de forma clara e objetiva\n- Use informações dos artigos fornecidos\n- Organize em tópicos quando apropriado\n- Cite os artigos relevantes\n- Se a informação não estiver nos artigos, mencione isso";

            $geminiResp = $http->post($geminiUrl, [
                'json' => [
                    'contents' => [[
                        'parts' => [['text' => $prompt]]
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ]
                ],
                'timeout' => 30
            ]);

            $geminiData = json_decode($geminiResp->getBody(), true);
            $resposta = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? 'Erro ao gerar resposta.';

            return response()->json([
                'pergunta' => $query,
                'artigos_encontrados' => count($hits),
                'artigos' => array_map(fn($h) => [
                    'titulo' => $h['titulo'] ?? 'N/A',
                    'autor'  => $h['autor'] ?? 'N/A',
                    'ano'    => $h['ano'] ?? 'N/A'
                ], $hits),
                'resposta' => $resposta
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Erro na API do Gemini: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao conectar com Gemini',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Erro no chat: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro interno',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

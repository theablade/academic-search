<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private $apiKey;
    private $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    // Modelos CORRETOS disponíveis em Outubro 2024:
    // - gemini-2.0-flash (rápido, recomendado)
    // - gemini-2.5-flash (mais recente)
    // - gemini-2.5-pro (mais inteligente)
    private $model = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');

        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY não configurada no .env');
            throw new \Exception('GEMINI_API_KEY não configurada');
        }
    }

    /**
     * Gera resposta de chat baseada em artigos (RAG)
     */
    public function generateChatResponse(string $pergunta, array $artigos): string
    {
        try {
            // Prepara o contexto com os artigos
            $contexto = $this->prepareContext($artigos);

            $prompt = "Você é um assistente acadêmico especializado. Responda à pergunta do usuário baseando-se APENAS nos artigos fornecidos abaixo.\n\n";
            $prompt .= "ARTIGOS DISPONÍVEIS:\n\n";
            $prompt .= $contexto . "\n\n";
            $prompt .= "PERGUNTA DO USUÁRIO: {$pergunta}\n\n";
            $prompt .= "INSTRUÇÕES:\n";
            $prompt .= "- Responda de forma clara e objetiva\n";
            $prompt .= "- Cite os artigos relevantes quando apropriado\n";
            $prompt .= "- Se a pergunta não puder ser respondida com os artigos disponíveis, diga isso claramente\n";
            $prompt .= "- Seja acadêmico mas acessível\n\n";
            $prompt .= "RESPOSTA:";

            return $this->callGeminiAPI($prompt);

        } catch (\Exception $e) {
            Log::error('Erro no generateChatResponse: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resume um artigo acadêmico
     */
    public function summarizeArticle(array $doc, string $lang = 'Português'): string
    {
        try {
            $titulo = $doc['titulo'] ?? 'Sem título';
            $autor = $doc['autor'] ?? 'Autor desconhecido';
            $ano = $doc['ano'] ?? 'Ano desconhecido';
            $resumo = $doc['resumo'] ?? '';
            $conteudo = $doc['conteudo'] ?? '';

            if (empty($conteudo)) {
                throw new \Exception('O artigo não possui conteúdo para resumir');
            }

            Log::info('Gerando resumo', [
                'titulo' => $titulo,
                'tamanho_conteudo' => strlen($conteudo),
                'idioma' => $lang
            ]);

            $prompt = "Você é um especialista em análise de artigos acadêmicos. Gere um resumo detalhado e estruturado do artigo abaixo.\n\n";
            $prompt .= "INFORMAÇÕES DO ARTIGO:\n";
            $prompt .= "Título: {$titulo}\n";
            $prompt .= "Autor: {$autor}\n";
            $prompt .= "Ano: {$ano}\n\n";

            if (!empty($resumo)) {
                $prompt .= "Resumo Original: {$resumo}\n\n";
            }

            $prompt .= "CONTEÚDO COMPLETO:\n{$conteudo}\n\n";
            $prompt .= "INSTRUÇÕES:\n";
            $prompt .= "- Gere um resumo em {$lang}\n";
            $prompt .= "- O resumo deve ter entre 150-250 palavras\n";
            $prompt .= "- Destaque os principais pontos, metodologia e conclusões\n";
            $prompt .= "- Use linguagem clara e acadêmica\n";
            $prompt .= "- Organize em parágrafos coesos\n";
            $prompt .= "- NÃO inclua cabeçalhos ou títulos, apenas o texto do resumo\n\n";
            $prompt .= "RESUMO:";

            $summary = $this->callGeminiAPI($prompt);

            Log::info('Resumo gerado com sucesso', [
                'tamanho_resumo' => strlen($summary)
            ]);

            return $summary;

        } catch (\Exception $e) {
            Log::error('Erro no summarizeArticle: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Chama a API do Gemini
     */
    private function callGeminiAPI(string $prompt): string
    {
        try {
            // IMPORTANTE: A API key deve ir na URL, não no body
            $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            Log::info('Chamando API Gemini', [
                'url' => "{$this->baseUrl}/models/{$this->model}:generateContent", // Log sem a key
                'model' => $this->model,
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::timeout(60)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                        ],
                    ]
                ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                Log::error('API Gemini retornou erro', [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                throw new \Exception("Erro na API Gemini: {$response->status()} - {$errorBody}");
            }

            $data = $response->json();

            // Extrai o texto da resposta
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data['candidates'][0]['content']['parts'][0]['text'];
                Log::info('Resposta recebida com sucesso', [
                    'response_length' => strlen($text)
                ]);
                return trim($text);
            }

            // Se foi bloqueado por segurança
            if (isset($data['candidates'][0]['finishReason']) &&
                $data['candidates'][0]['finishReason'] === 'SAFETY') {
                throw new \Exception('Conteúdo bloqueado por questões de segurança');
            }

            Log::error('Resposta da API em formato inesperado', ['data' => $data]);
            throw new \Exception('Resposta da API em formato inesperado');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Erro de conexão com API Gemini: ' . $e->getMessage());
            throw new \Exception('Erro de conexão com o serviço de IA. Tente novamente.');
        } catch (\Exception $e) {
            Log::error('Erro ao chamar API Gemini: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepara contexto dos artigos para o chat
     */
    private function prepareContext(array $artigos): string
    {
        $contexto = '';
        $count = 0;

        foreach ($artigos as $artigo) {
            $count++;
            $source = $artigo['_source'] ?? $artigo;

            $titulo = $source['titulo'] ?? 'Sem título';
            $autor = $source['autor'] ?? 'Autor desconhecido';
            $ano = $source['ano'] ?? '';
            $resumo = $source['resumo'] ?? '';
            $conteudo = $source['conteudo'] ?? '';

            $contexto .= "--- ARTIGO {$count} ---\n";
            $contexto .= "Título: {$titulo}\n";
            $contexto .= "Autor: {$autor}\n";
            $contexto .= "Ano: {$ano}\n";

            if (!empty($resumo)) {
                $contexto .= "Resumo: {$resumo}\n";
            }

            if (!empty($conteudo)) {
                // Limita o tamanho do conteúdo para não estourar o limite de tokens
                $conteudoLimitado = substr($conteudo, 0, 3000);
                if (strlen($conteudo) > 3000) {
                    $conteudoLimitado .= '... [conteúdo truncado]';
                }
                $contexto .= "Conteúdo: {$conteudoLimitado}\n";
            }

            $contexto .= "\n";

            // Limita a 5 artigos para não exceder o limite de tokens
            if ($count >= 5) {
                break;
            }
        }

        return $contexto;
    }
}

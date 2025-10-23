<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;


use Exception;

class ElasticsearchService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST')])
            ->setApiKey(env('ELASTIC_API_KEY'))
            ->build();
    }


   public function searchDocuments(string $index, string $query = null): array
{
    try {
        if (!$query) {
            $body = ['query' => ['match_all' => (object) []]];
        } else {
            $body = [
                'query' => [
                    'bool' => [
                        'should' => [

                            [
                                'multi_match' => [
                                    'query'  => $query,
                                    'fields' => ['titulo^3', 'resumo^2'],
                                    'type'   => 'best_fields',
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
        }

        $response = $this->client->search([
            'index' => $index,
            'body'  => $body,
        ]);

        $responseArray = method_exists($response, 'asArray')
            ? $response->asArray()
            : (array) $response;

        return $responseArray['hits']['hits'] ?? [];
    } catch (\Exception $e) {
        Log::error('Erro na busca Elasticsearch: ' . $e->getMessage());
        throw $e;
    }
}

 public function indexDocument(string $index, array $data): array
    {
        try {
            $response = $this->client->index([
                'index' => $index,
                'body'  => $data,
            ]);

            return method_exists($response, 'asArray')
                ? $response->asArray()
                : (array) $response;
        } catch (Exception $e) {
            Log::error("Erro ao indexar documento no Elasticsearch: {$e->getMessage()}");
            throw $e;
        }
    }

    public function getDocument(string $index, string $id): array
    {
        try {
            $response = $this->client->get([
                'index' => $index,
                'id' => $id
            ]);

            return method_exists($response, 'asArray')
                ? $response->asArray()
                : (array) $response;
        } catch (\Exception $e) {
            Log::error("Documento não encontrado: {$id} no índice {$index}");
            throw $e;
        }
    }

    /**
     * Delete a document by ID
     */
    public function deleteDocument(string $index, string $id): array
    {
        try {
            $response = $this->client->delete([
                'index' => $index,
                'id' => $id
            ]);

            return method_exists($response, 'asArray')
                ? $response->asArray()
                : (array) $response;
        } catch (\Exception $e) {
            Log::error("Erro ao deletar documento: {$id} do índice {$index}");
            throw $e;
        }
    }


}

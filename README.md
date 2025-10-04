# 🧠 Academic Search + Chat AI

[![Laravel](https://img.shields.io/badge/Laravel-8.x-red?logo=laravel)](https://laravel.com/docs)  
[![Elasticsearch](https://img.shields.io/badge/Elasticsearch-PHP-blue?logo=elasticsearch)](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html)  
[![Google Gemini API](https://img.shields.io/badge/Google-Gemini-green?logo=google)](https://developers.generativeai.google/)

Projeto Laravel + Elasticsearch + Google Gemini para gerenciar artigos acadêmicos e fornecer um chat inteligente baseado em conteúdo.

---

## 💡 Visão geral

Este projeto demonstra como combinar **Elasticsearch** com **IA generativa (Gemini API)** para criar uma solução de busca e chat acadêmico:

- Indexação de artigos acadêmicos.
- Busca híbrida: textual + semântica.
- Chat RAG (Retrieval-Augmented Generation) baseado nos artigos indexados.
- Respostas contextuais e organizadas em tópicos, citando os artigos.

---
## ✨ Funcionalidades

- 📄 Cadastro e armazenamento de artigos no Elasticsearch
- 🔍 Busca textual e semântica híbrida em títulos, resumos e conteúdos
- 🤖 Chat RAG baseado nos artigos encontrados
- 📝 Respostas claras, objetivas e com referências
- ⚡ API RESTful pronta para integração com front-end

## 🚀 Tecnologias

- **Backend:** Laravel 10
- **Busca:** Elasticsearch
- **HTTP Client:** GuzzleHttp
- **IA generativa:** Google Gemini API
- **Banco de dados:** qualquer para Laravel, Elasticsearch armazena os artigos
- **Front-end:** opcional, pode usar Vue.js, React ou Flutter

---

## ⚙️ Instalação

1. Clone o repositório:

```bash
git clone https://github.com/theablade/academic-search.git
cd academic-search
```

2. Instale dependências do Laravel
```bash
composer install

```

3. Configure o arquivo .env
```bash
APP_NAME=AcademicSearch
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

ELASTICSEARCH_HOST=http://localhost:9200
ELASTIC_API_KEY=<sua-chave-elastic>
GEMINI_API_KEY=<sua-chave-gemini>

```
4. Execute o servidor Laravel:
php artisan serve


## 🚀 Rotas da API
Prefixo: `/api/artigos`
| Método | Rota               | Descrição                                      |
|--------|-------------------|-----------------------------------------------|
| POST   | `/`               | Criar um novo artigo                           |
| GET    | `/`               | Listar todos os artigos                        |
| GET    | `/search/{query}` | Buscar artigos por palavra-chave              |
| POST   | `/chat`           | Perguntar algo baseado nos artigos (RAG)     |

## Exemplo JSON - **Criar artigo:**

Request
```bash
POST /api/artigos

{
  "titulo": "Inteligência Artificial na Educação",
  "autor": "Fulstak",
  "ano": 2025,
  "resumo": "Resumo do artigo...",
  "conteudo": "Conteúdo completo do artigo..."
}


```

Response:
```bash
{
  "_index": "artigos",
  "_id": "123456",
  "_version": 1,
  "result": "created",
  "_shards": { "total": 2, "successful": 1, "failed": 0 }
}
```

## Exemplo JSON - Buscar Artigos

Request
```bash
GET /api/artigos/search/inteligência


```

Response:
```bash
[
  {
    "_index": "artigos",
    "_id": "123456",
    "_source": {
      "titulo": "Inteligência Artificial na Educação",
      "autor": "Fulstak",
      "ano": 2025,
      "resumo": "Resumo do artigo...",
      "conteudo": "Conteúdo completo do artigo..."
    }
  }
]
```

## Exemplo JSON - Chat RAG

Request
```bash
{
  "pergunta": "Quais são os principais desafios da IA na educação?"
}



```

Response:
```bash
{
  "pergunta": "Quais são os principais desafios da IA na educação?",
  "artigos_encontrados": 3,
  "artigos": [
    { "titulo": "Inteligência Artificial na Educação", "autor": "Fulstak", "ano": 2025 },
    { "titulo": "Educação e IA", "autor": "Outro Autor", "ano": 2023 }
  ],
  "resposta": "1. Desafio 1: Implementação técnica\n2. Desafio 2: Capacitação de professores\n3. Desafio 3: Ética e privacidade dos dados..."
}
```

🔧 Observações Técnicas

O campo conteudo deve ser indexado como semantic_text no Elasticsearch para suportar buscas semânticas.

Busca híbrida: combina multi_match em titulo e resumo + busca semântica em conteudo.

O chat RAG utiliza Gemini API para gerar respostas com base no conteúdo dos artigos.

Logging de erros via Log::error para debug.

📈 Sugestões de Melhorias

Criar front-end interativo para chat e visualização de artigos (Vue.js, React ou Flutter).

Implementar autenticação de usuários para gerenciar artigos privados.

Permitir upload de PDFs e extração automática de conteúdo.

Melhorar ranking das respostas do chat usando ponderação semântica.

Cache de respostas para reduzir chamadas à API do Gemini.

🏁 Conclusão

Este projeto é uma solução completa de busca e chat acadêmico, que demonstra:

Indexação e busca avançada com Elasticsearch.

Integração de IA generativa com Google Gemini.

Geração de respostas contextuais baseadas em artigos científicos.

Base sólida para projetos educativos, acadêmicos e corporativos.

📚 Referências
 [Laravel Documentation](https://laravel.com/docs)
- [Elasticsearch PHP Client](https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/index.html)
- [Google Gemini API](https://developers.generativeai.google/)
    


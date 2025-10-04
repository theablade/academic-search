<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-lg">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold text-gray-900">
                
            </h1>
            <p class="text-sm text-gray-500">Elastic Search + Google Gemini AI</p>
          </div>
        </div>
        <div class="flex gap-2">
          <div class="flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-full">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
            </svg>
            <span class="text-xs font-medium text-blue-700">Elasticsearch</span>
          </div>
          <div class="flex items-center gap-2 bg-purple-50 px-3 py-1 rounded-full">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="text-xs font-medium text-purple-700">Gemini AI</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Chat Messages -->
    <div ref="chatContainer" class="flex-1 max-w-5xl w-full mx-auto px-6 py-6 overflow-y-auto">
      <div class="space-y-6">
        <div v-for="(msg, idx) in messages" :key="idx" 
             :class="['flex', msg.type === 'user' ? 'justify-end' : 'justify-start']">
          <div :class="[
            'max-w-3xl rounded-2xl px-5 py-4 shadow-sm',
            msg.type === 'user' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200'
          ]">
            <!-- Assistant Header -->
            <div v-if="msg.type === 'assistant'" class="flex items-center gap-2 mb-3">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
              <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">AI Assistant</span>
            </div>

            <!-- Message Text -->
            <div :class="[
              'whitespace-pre-wrap leading-relaxed',
              msg.type === 'user' ? 'text-white' : 'text-gray-800'
            ]">
              {{ msg.text }}
            </div>

            <!-- Articles Found -->
            <div v-if="msg.articles && msg.articles.length > 0" class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
                <span class="text-xs font-semibold text-gray-600">
                  {{ msg.count }} {{ msg.count === 1 ? 'artigo encontrado' : 'artigos encontrados' }}
                </span>
              </div>
              <div class="space-y-2">
                <div v-for="(article, i) in msg.articles" :key="i" 
                     class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                  <div class="font-semibold text-sm text-gray-900">{{ article.titulo }}</div>
                  <div class="text-xs text-gray-600 mt-1">{{ article.autor }} • {{ article.ano }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading Indicator -->
        <div v-if="loading" class="flex justify-start">
          <div class="bg-white border border-gray-200 rounded-2xl px-5 py-4 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="animate-spin rounded-full h-5 w-5 border-2 border-indigo-600 border-t-transparent"></div>
              <span class="text-sm text-gray-600">Pesquisando e gerando resposta...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t border-gray-200 shadow-lg">
      <div class="max-w-5xl mx-auto px-6 py-4">
        <div class="flex gap-3">
          <input
            v-model="input"
            @keypress.enter="sendMessage"
            type="text"
            placeholder="Faça uma pergunta sobre os artigos acadêmicos..."
            class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :disabled="loading"
          />
          <button
            @click="sendMessage"
            :disabled="loading || !input.trim()"
            class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2 font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Enviar
          </button>
        </div>
        
        <!-- Example Questions -->
        <div class="mt-3 flex flex-wrap gap-2">
          <button
            v-for="example in exampleQuestions"
            :key="example"
            @click="input = example"
            class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full transition-colors"
          >
            {{ example }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChatRAG',
  data() {
    return {
      messages: [
        {
          type: 'assistant',
          text: 'Olá! 👋 Sou seu assistente de pesquisa acadêmica.\n\nFaça perguntas sobre os artigos no banco de dados e responderei com base no conteúdo científico disponível.\n\nPosso ajudá-lo a:\n• Encontrar informações específicas\n• Comparar diferentes estudos\n• Resumir conteúdos complexos\n• Identificar tendências e padrões',
          articles: []
        }
      ],
      input: '',
      loading: false,
      exampleQuestions: [
        'Como a IA está transformando a educação?',
        'Quais os benefícios da telemedicina?',
        'Impactos da tecnologia no meio ambiente'
      ],
      apiUrl: 'http://127.0.0.1:8000/api'
    };
  },
  methods: {
    async sendMessage() {
      if (!this.input.trim() || this.loading) return;

      const userMessage = this.input;
      this.input = '';
      
      // Add user message
      this.messages.push({ type: 'user', text: userMessage });
      this.loading = true;
      
      // Scroll to bottom
      this.$nextTick(() => {
        this.scrollToBottom();
      });

      try {
        const response = await fetch(`${this.apiUrl}/artigos/chat`, {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ pergunta: userMessage })
        });

        if (!response.ok) {
          throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();

        // Add assistant response
        this.messages.push({
          type: 'assistant',
          text: data.resposta || 'Desculpe, não consegui gerar uma resposta.',
          articles: data.artigos || [],
          count: data.artigos_encontrados || 0
        });
      } catch (error) {
        console.error('Erro:', error);
        this.messages.push({
          type: 'assistant',
          text: '❌ Erro ao conectar com o servidor.\n\nVerifique se:\n• a sua ligação à internet está ativa\n• o servidor backend está em execução',
          articles: []
        });
      } finally {
        this.loading = false;
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    
    scrollToBottom() {
      const container = this.$refs.chatContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    }
  }
};
</script>

<style scoped>
.overflow-y-auto {
  scroll-behavior: smooth;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.space-y-6 > div {
  animation: fadeIn 0.3s ease-out;
}
</style>
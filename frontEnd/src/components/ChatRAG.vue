<!-- ChatRAG.vue - Componente de Chat com IA -->
<template>
  <div class="max-w-5xl mx-auto px-6 py-8">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <!-- Header -->
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="bg-white/20 p-2 rounded-lg">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-white">Chat com IA</h2>
            <p class="text-purple-100 text-sm">Pergunte sobre os artigos acadêmicos</p>
          </div>
        </div>
      </div>

      <!-- Chat Messages -->
      <div ref="messagesContainer" class="h-[500px] overflow-y-auto p-6 space-y-4 bg-gray-50">
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="text-center py-12">
          <div class="bg-gradient-to-br from-purple-100 to-indigo-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Olá! Como posso ajudar?</h3>
          <p class="text-gray-600">Faça perguntas sobre os artigos acadêmicos ou solicite resumos</p>
        </div>

        <!-- Messages -->
        <div
          v-for="(message, index) in messages"
          :key="index"
          :class="[
            'flex',
            message.role === 'user' ? 'justify-end' : 'justify-start'
          ]"
        >
          <div
            :class="[
              'max-w-[80%] rounded-lg px-4 py-3',
              message.role === 'user'
                ? 'bg-blue-600 text-white'
                : 'bg-white border border-gray-200'
            ]"
          >
            <!-- User Message -->
            <div v-if="message.role === 'user'">
              <p class="text-sm">{{ message.content }}</p>
            </div>

            <!-- Assistant Message -->
            <div v-else>
              <!-- Summary Type -->
              <div v-if="message.type === 'summary'" class="space-y-3">
                <!-- Article Info -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-4 mb-3 border border-purple-200">
                  <div class="flex items-start gap-3">
                    <div class="bg-purple-600 p-2 rounded-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <h4 class="font-semibold text-gray-900 mb-1">{{ message.meta.titulo }}</h4>
                      <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                        <span class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                          {{ message.meta.autor }}
                        </span>
                        <span class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                          {{ message.meta.ano }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Summary Content -->
                <div class="prose prose-sm max-w-none">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h5 class="font-semibold text-gray-900">Resumo Gerado por IA:</h5>
                  </div>
                  <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ message.content }}</p>
                </div>
              </div>

              <!-- Chat Type -->
              <div v-else-if="message.type === 'chat'" class="space-y-3">
                <p class="text-gray-800 leading-relaxed whitespace-pre-wrap">{{ message.content }}</p>

                <!-- Referenced Articles -->
                <div v-if="message.articles && message.articles.length > 0" class="mt-4 pt-4 border-t border-gray-200">
                  <p class="text-sm font-medium text-gray-700 mb-2">📚 Artigos Referenciados ({{ message.articles_count }}):</p>
                  <div class="space-y-2">
                    <div
                      v-for="(artigo, idx) in message.articles"
                      :key="idx"
                      class="bg-gray-50 rounded-lg p-3 text-sm"
                    >
                      <p class="font-medium text-gray-900">{{ artigo.titulo }}</p>
                      <p class="text-gray-600 text-xs mt-1">{{ artigo.autor }} ({{ artigo.ano }})</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-start">
          <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 max-w-[80%]">
            <div class="flex items-center gap-2">
              <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
              <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
              <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="border-t border-gray-200 p-4 bg-white">
        <form @submit.prevent="sendMessage" class="flex gap-3">
          <input
            v-model="userInput"
            type="text"
            :disabled="loading"
            placeholder="Digite sua pergunta sobre os artigos..."
            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
          />
          <button
            type="submit"
            :disabled="loading || !userInput.trim()"
            class="px-6 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Enviar
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ChatRAG',
  data() {
    return {
      messages: [],
      userInput: '',
      loading: false
    };
  },
  methods: {
    async sendMessage() {
      if (!this.userInput.trim() || this.loading) return;

      const pergunta = this.userInput.trim();

      // Adiciona mensagem do usuário
      this.messages.push({
        role: 'user',
        content: pergunta
      });

      this.userInput = '';
      this.loading = true;

      try {
        const response = await axios.post('/api/artigos/chat', { pergunta });

        // Adiciona resposta da IA
        this.messages.push({
          role: 'assistant',
          type: 'chat',
          content: response.data.resposta,
          articles: response.data.artigos || [],
          articles_count: response.data.artigos_encontrados || 0
        });

      } catch (error) {
        console.error('Erro no chat:', error);
        this.messages.push({
          role: 'assistant',
          type: 'chat',
          content: '❌ Desculpe, ocorreu um erro ao processar sua pergunta. Tente novamente.'
        });
      } finally {
        this.loading = false;
        this.scrollToBottom();
      }
    },

    async summarizeArticle(articleData) {
      this.loading = true;

      // Adiciona mensagem indicando que está gerando resumo
      this.messages.push({
        role: 'user',
        content: `Gerar resumo do artigo: "${articleData.titulo}"`
      });

      try {
        console.log('Enviando para resumo:', articleData);

        const response = await axios.post('/api/artigos/summarize', {
          doc: articleData,
          lang: 'Português'
        });

        console.log('Resposta do resumo:', response.data);

        // Adiciona resumo na conversa
        this.messages.push({
          role: 'assistant',
          type: 'summary',
          content: response.data.summary,
          meta: response.data.meta
        });

      } catch (error) {
        console.error('Erro completo ao gerar resumo:', error);
        console.error('Resposta do erro:', error.response?.data);
        console.error('Status do erro:', error.response?.status);

        let errorMessage = '❌ Desculpe, ocorreu um erro ao gerar o resumo.';

        if (error.response?.data?.details) {
          errorMessage += '\n\nDetalhes: ' + error.response.data.details;
        } else if (error.response?.data?.error) {
          errorMessage += '\n\n' + error.response.data.error;
        } else if (error.message) {
          errorMessage += '\n\n' + error.message;
        }

        this.messages.push({
          role: 'assistant',
          type: 'chat',
          content: errorMessage
        });
      } finally {
        this.loading = false;
        this.scrollToBottom();
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    }
  }
};
</script>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-0.5rem);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}

.prose {
  color: #374151;
}

.prose p {
  margin-bottom: 0;
}
</style>

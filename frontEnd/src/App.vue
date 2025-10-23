<!-- App.vue - Componente Principal -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Banco de Artigos Acadêmicos</h1>
              <p class="text-sm text-gray-500">Elasticsearch + Google Gemini AI</p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2">
          <button
            @click="currentView = 'artigos'"
            :class="[
              'px-6 py-2 rounded-lg font-medium  flex items-center  gap-2 transition-colors',
              currentView === 'artigos'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
        <span class="material-symbols-outlined">
                article
                </span>
          Artigos
          </button>
          <button
            @click="currentView = 'chat'"
            :class="[
              'px-6 py-2 rounded-lg font-medium flex items-center  gap-2 transition-colors',
              currentView === 'chat'
                ? 'bg-purple-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            <span class="material-symbols-outlined">
                chat
                </span>
          Chat com IA
          </button>
          <button
            @click="showAddModal = true"
            class="ml-auto px-6 py-2 rounded-lg font-medium bg-blue-600 text-white hover:bg-blue-700 transition-colors flex items-center gap-2"
          >

        <span class="material-symbols-outlined">
        add
</span>
            Adicionar Artigo
          </button>
        </div>
      </div>
    </nav>

    <!-- Modal Adicionar Artigo -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-900">Adicionar Novo Artigo</h2>
          <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="handleAddArticle" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
            <input
              v-model="newArticle.titulo"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Digite o título do artigo"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Autor *</label>
              <input
                v-model="newArticle.autor"
                type="text"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Nome do autor"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Ano *</label>
              <input
                v-model="newArticle.ano"
                type="number"
                required
                min="1900"
                max="2099"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="2024"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Resumo *</label>
            <textarea
              v-model="newArticle.resumo"
              required
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Digite um resumo do artigo"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Conteúdo Completo *</label>
            <textarea
              v-model="newArticle.conteudo"
              required
              rows="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Digite o conteúdo completo do artigo"
            ></textarea>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <span v-if="loading" class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ loading ? 'Salvando...' : 'Salvar Artigo' }}
            </button>
            <button
              type="button"
              @click="closeAddModal"
              class="px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Views -->
    <ArtigosList
      v-show="currentView === 'artigos'"
      :key="articlesKey"
      @summarize-article="handleSummarizeArticle"
    />

    <ChatRAG
      v-show="currentView === 'chat'"
      ref="chatComponent"
    />

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-gray-600">
        <p>
          Desenvolvido por
          <strong>
            <a href="https://github.com/theablade" class="text-blue-600 hover:underline" target="_blank">
              Fernando Muethea
            </a>
          </strong>
        </p>
        <p>Powered by <strong>Elasticsearch</strong> + <strong>Google Gemini AI</strong></p>
        <p class="mt-1">Desafio Google Cloud + Elastic</p>
      </div>
    </footer>
  </div>
</template>

<script>
import ArtigosList from './components/ArtigosList.vue';
import ChatRAG from './components/ChatRAG.vue';
import axios from 'axios';

export default {
  name: 'App',
  components: {
    ArtigosList,
    ChatRAG
  },
  data() {
    return {
      currentView: 'artigos',
      showAddModal: false,
      loading: false,
      articlesKey: 0,
      newArticle: {
        titulo: '',
        autor: '',
        ano: '',
        resumo: '',
        conteudo: ''
      }
    };
  },
  methods: {
    async handleSummarizeArticle(articleData) {
      // Muda para a view do chat
      this.currentView = 'chat';

      // Aguarda o componente renderizar
      await this.$nextTick();

      // Chama o método de resumo do componente Chat
      if (this.$refs.chatComponent) {
        this.$refs.chatComponent.summarizeArticle(articleData);
      }
    },

    async handleAddArticle() {
      this.loading = true;
      try {
        const response = await axios.post('/api/artigos', this.newArticle);

        // Exibe mensagem de sucesso
        alert('Artigo adicionado com sucesso!');

        // Fecha o modal
        this.closeAddModal();

        // Atualiza a lista de artigos
        this.articlesKey++;

      } catch (error) {
        console.error('Erro ao adicionar artigo:', error);
        alert('Erro ao adicionar artigo: ' + (error.response?.data?.message || error.message));
      } finally {
        this.loading = false;
      }
    },

    closeAddModal() {
      this.showAddModal = false;
      this.newArticle = {
        titulo: '',
        autor: '',
        ano: '',
        resumo: '',
        conteudo: ''
      };
    }
  }
};
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>

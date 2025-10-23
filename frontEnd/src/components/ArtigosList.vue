<!-- ArtigosList.vue - Componente de Lista de Artigos -->
<template>
  <div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Search Bar -->
    <div class="mb-8">
      <div class="relative">
        <input
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="🔍 Buscar artigos por título, autor, conteúdo..."
          class="w-full px-6 py-4 pr-12 text-lg border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
        />
        <div v-if="searching" class="absolute right-4 top-1/2 transform -translate-y-1/2">
          <div class="w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>

      <!-- Search Results Info -->
      <div v-if="searchQuery && !searching && artigos.length > 0" class="mt-3 text-sm text-gray-600">
        Encontrados <strong>{{ artigos.length }}</strong> resultados para "<strong>{{ searchQuery }}</strong>"
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="mt-4 text-gray-600">Carregando artigos...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="artigos.length === 0" class="text-center py-12">
      <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-800 mb-2">Nenhum artigo encontrado</h3>
      <p class="text-gray-600">{{ searchQuery ? 'Tente buscar com outros termos' : 'Adicione o primeiro artigo' }}</p>
    </div>

    <!-- Articles Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="artigo in artigos"
        :key="artigo._id"
        class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-shadow overflow-hidden border border-gray-200"
      >
        <!-- Relevance Badge (only show in search results) -->
        <div v-if="searchQuery && artigo._score" class="bg-gradient-to-r from-green-500 to-emerald-500 px-4 py-2 flex items-center justify-between">
          <span class="text-white text-xs font-semibold">Relevância</span>
          <div class="flex items-center gap-2">
            <div class="bg-white bg-opacity-20 rounded-full h-2 w-24 overflow-hidden">
              <div
                class="bg-white h-full rounded-full transition-all duration-500"
                :style="{ width: getRelevancePercentage(artigo) + '%' }"
              ></div>
            </div>
            <span class="text-white font-bold text-sm">{{ getRelevancePercentage(artigo) }}%</span>
          </div>
        </div>

        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-4">
          <h3 class="text-lg font-bold text-white line-clamp-2">{{ getSource(artigo).titulo }}</h3>
        </div>

        <!-- Card Body -->
        <div class="p-5 space-y-4">
          <!-- Meta Info -->
          <div class="flex flex-wrap gap-3 text-sm text-gray-600">
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              {{ getSource(artigo).autor }}
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              {{ getSource(artigo).ano }}
            </span>
          </div>

          <!-- Resumo -->
          <p class="text-gray-700 text-sm line-clamp-3">
            {{ getSource(artigo).resumo }}
          </p>

          <!-- Actions -->
          <div class="flex gap-2 pt-2">
            <button
              @click="viewDetails(artigo)"
              class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition-colors flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Ver
            </button>
            <button
              @click="askAI(artigo)"
              class="px-4 py-2 bg-purple-50 text-purple-600 rounded-lg font-medium hover:bg-purple-100 transition-colors flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
              Perguntar IA
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-900">{{ selectedArticle.titulo }}</h2>
            <!-- Show relevance in modal if available -->
            <div v-if="selectedArticle._score && searchQuery" class="mt-2 flex items-center gap-2">
              <span class="text-sm text-gray-600">Relevância da busca:</span>
              <div class="flex items-center gap-2">
                <div class="bg-gray-200 rounded-full h-2 w-32 overflow-hidden">
                  <div
                    class="bg-green-500 h-full rounded-full"
                    :style="{ width: getRelevancePercentage(selectedArticle) + '%' }"
                  ></div>
                </div>
                <span class="text-sm font-bold text-green-600">{{ getRelevancePercentage(selectedArticle) }}%</span>
              </div>
            </div>
          </div>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 ml-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-6">
          <!-- Meta Info -->
          <div class="flex flex-wrap gap-4 text-gray-600">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span><strong>Autor:</strong> {{ selectedArticle.autor }}</span>
            </div>
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span><strong>Ano:</strong> {{ selectedArticle.ano }}</span>
            </div>
          </div>

          <!-- Resumo -->
          <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-r-lg">
            <h3 class="font-semibold text-gray-900 mb-2">📝 Resumo</h3>
            <p class="text-gray-700 leading-relaxed">{{ selectedArticle.resumo }}</p>
          </div>

          <!-- Conteúdo -->
          <div>
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Conteúdo Completo
            </h3>
            <div class="prose max-w-none">
              <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ selectedArticle.conteudo }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ArtigosList',
  data() {
    return {
      artigos: [],
      loading: true,
      searching: false,
      searchQuery: '',
      searchTimeout: null,
      showModal: false,
      selectedArticle: {},
      maxScore: 0
    };
  },
  mounted() {
    this.fetchArticles();
  },
  methods: {
    async fetchArticles() {
      this.loading = true;
      try {
        const response = await axios.get('/api/artigos');
        this.artigos = response.data;
        this.maxScore = 0; // Reset max score when not searching
      } catch (error) {
        console.error('Erro ao buscar artigos:', error);
        alert('Erro ao carregar artigos');
      } finally {
        this.loading = false;
      }
    },

    handleSearch() {
      clearTimeout(this.searchTimeout);

      if (!this.searchQuery.trim()) {
        this.fetchArticles();
        return;
      }

      this.searching = true;
      this.searchTimeout = setTimeout(async () => {
        try {
          const response = await axios.get(`/api/artigos/search/${encodeURIComponent(this.searchQuery)}`);
          this.artigos = response.data;

          // Calculate max score for percentage calculation
          if (this.artigos.length > 0) {
            this.maxScore = Math.max(...this.artigos.map(a => a._score || 0));
          }
        } catch (error) {
          console.error('Erro na busca:', error);
        } finally {
          this.searching = false;
        }
      }, 500);
    },

    getRelevancePercentage(artigo) {
      if (!artigo._score || this.maxScore === 0) return 0;
      // Calculate percentage relative to max score
      return Math.round((artigo._score / this.maxScore) * 100);
    },

    getSource(artigo) {
      return artigo._source || artigo;
    },

    viewDetails(artigo) {
      this.selectedArticle = {
        ...this.getSource(artigo),
        _score: artigo._score // Preserve score for modal
      };
      this.showModal = true;
    },

    askAI(artigo) {
      const articleData = this.getSource(artigo);
      this.$emit('summarize-article', articleData);
    },

    async deleteArticle(artigo) {
      if (!confirm('Tem certeza que deseja deletar este artigo?')) {
        return;
      }

      try {
        await axios.delete(`/api/artigos/${artigo._id}`);
        this.artigos = this.artigos.filter(a => a._id !== artigo._id);
        alert('Artigo deletado com sucesso!');
      } catch (error) {
        console.error('Erro ao deletar artigo:', error);
        alert('Erro ao deletar artigo');
      }
    }
  }
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.prose {
  max-width: none;
}
</style>

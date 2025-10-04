<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
          📚 Banco de Artigos Acadêmicos
        </h1>
        <p class="text-gray-600">
          Pesquise e explore artigos com busca híbrida (keyword + semântica)
        </p>
      </div>

      <!-- Search Bar -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex gap-3">
          <input
            v-model="searchQuery"
            @keypress.enter="searchArtigos"
            type="text"
            placeholder="Buscar artigos... (ex: inteligência artificial, educação, saúde)"
            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <button
            @click="searchArtigos"
            :disabled="loading"
            class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors flex items-center gap-2"
          >
            <svg v-if="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <div v-else class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            {{ loading ? 'Buscando...' : 'Buscar' }}
          </button>
          <button
            @click="loadAllArtigos"
            class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors"
          >
            Todos
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="text-center">
          <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-gray-600">Carregando artigos...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-red-800 font-semibold mb-2">Erro ao carregar artigos</p>
        <p class="text-red-600 text-sm">{{ error }}</p>
        <button
          @click="loadAllArtigos"
          class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700"
        >
          Tentar Novamente
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="artigos.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhum artigo encontrado</h3>
        <p class="text-gray-500">Tente outra busca ou adicione novos artigos ao banco de dados.</p>
      </div>

      <!-- Articles Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="(artigo, index) in artigos"
          :key="index"
          class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-200"
        >
          <!-- Score (se houver busca) -->
          <div v-if="artigo._score" class="flex items-center gap-2 mb-3">
            <div class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">
              Relevância: {{ Math.round(artigo._score * 100) / 100 }}
            </div>
          </div>

          <!-- Título -->
          <h3 class="text-lg font-bold text-gray-900 mb-3 line-clamp-2">
            {{ artigo._source?.titulo || artigo.titulo }}
          </h3>

          <!-- Metadata -->
          <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <span>{{ artigo._source?.autor || artigo.autor }}</span>
            </div>
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ artigo._source?.ano || artigo.ano }}</span>
            </div>
          </div>

          <!-- Resumo -->
          <p class="text-gray-700 text-sm mb-4 line-clamp-3">
            {{ artigo._source?.resumo || artigo.resumo }}
          </p>

          <!-- Actions -->
          <div class="flex gap-2">
            <button
              @click="viewDetails(artigo)"
              class="flex-1 bg-blue-50 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
            >
              Ver Detalhes
            </button>
            <button
              @click="askAboutArticle(artigo)"
              class="bg-purple-50 text-purple-600 px-4 py-2 rounded-lg hover:bg-purple-100 transition-colors text-sm font-medium"
            >
              Perguntar IA
            </button>
          </div>
        </div>
      </div>

      <!-- Results Count -->
      <div v-if="artigos.length > 0" class="mt-6 text-center text-gray-600">
        Mostrando {{ artigos.length }} {{ artigos.length === 1 ? 'artigo' : 'artigos' }}
      </div>
    </div>

    <!-- Modal de Detalhes -->
    <div
      v-if="selectedArtigo"
      @click="selectedArtigo = null"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    >
      <div
        @click.stop
        class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto p-8"
      >
        <div class="flex justify-between items-start mb-6">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ selectedArtigo._source?.titulo || selectedArtigo.titulo }}
          </h2>
          <button
            @click="selectedArtigo = null"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Autor</h3>
            <p class="text-gray-900">{{ selectedArtigo._source?.autor || selectedArtigo.autor }}</p>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Ano</h3>
            <p class="text-gray-900">{{ selectedArtigo._source?.ano || selectedArtigo.ano }}</p>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Resumo</h3>
            <p class="text-gray-700 leading-relaxed">
              {{ selectedArtigo._source?.resumo || selectedArtigo.resumo }}
            </p>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-1">Conteúdo</h3>
            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">
              {{ selectedArtigo._source?.conteudo || selectedArtigo.conteudo }}
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
  <div class="max-w-7xl mx-auto px-2 py-6 text-center text-sm text-gray-600">
    <p>🚀 Desenvolvido por <strong>
        <a href="https://github.com/theablade" class="text-blue-600 hover:underline" target="_blank">Fernando Muethea</a>.
     </strong></p>
    <p>Powered by <strong>Elasticsearch</strong> + <strong>Google Gemini AI</strong></p>
    <p class="mt-1">Desafio Google Cloud + Elastic</p>
  </div>
</footer>

  </div>
</template>

<script>
export default {
  name: 'ArtigosList',
  data() {
    return {
      artigos: [],
      searchQuery: '',
      loading: false,
      error: null,
      selectedArtigo: null,
      apiUrl: 'http://127.0.0.1:8000/api'
    };
  },
  mounted() {
    this.loadAllArtigos();
  },
  methods: {
    async loadAllArtigos() {
      this.loading = true;
      this.error = null;
      this.searchQuery = '';

      try {
        const response = await fetch(`${this.apiUrl}/artigos`);
        
        if (!response.ok) {
          throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        this.artigos = data;
      } catch (err) {
        console.error('Erro ao carregar artigos:', err);
        this.error = err.message || 'Não foi possível conectar ao servidor';
        this.artigos = [];
      } finally {
        this.loading = false;
      }
    },

    async searchArtigos() {
      if (!this.searchQuery.trim()) {
        this.loadAllArtigos();
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await fetch(
          `${this.apiUrl}/artigos/search/${encodeURIComponent(this.searchQuery)}`
        );
        
        if (!response.ok) {
          throw new Error(`Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        this.artigos = data;

        if (data.length === 0) {
          this.error = `Nenhum resultado para "${this.searchQuery}"`;
        }
      } catch (err) {
        console.error('Erro na busca:', err);
        this.error = err.message || 'Erro ao realizar busca';
        this.artigos = [];
      } finally {
        this.loading = false;
      }
    },

    viewDetails(artigo) {
      this.selectedArtigo = artigo;
    },

    askAboutArticle(artigo) {
      const titulo = artigo._source?.titulo || artigo.titulo;
      // Redirecionar para o chat com a pergunta pré-preenchida
      this.$emit('open-chat', `Me fale sobre o artigo: ${titulo}`);
      // OU se estiver usando router:
      // this.$router.push({ name: 'chat', query: { q: `Me fale sobre: ${titulo}` } });
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
</style>
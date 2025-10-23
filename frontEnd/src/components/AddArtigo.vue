<template>
  <div
    v-if="show"
    @click="$emit('close')"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
  >
    <div
      @click.stop
      class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto p-8 animate-modal"
    >
      <div class="flex justify-between items-start mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">➕ Adicionar Novo Artigo</h2>
          <p class="text-sm text-gray-500 mt-1">Preencha todos os campos obrigatórios</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="submitArticle" class="space-y-5">
        <!-- Título -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            Título <span class="text-red-500">*</span>
          </label>
          <input
            v-model="article.titulo"
            type="text"
            required
            placeholder="Ex: Inteligência Artificial na Educação Moderna"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          />
        </div>

        <!-- Autor e Ano na mesma linha -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Autor -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Autor <span class="text-red-500">*</span>
            </label>
            <input
              v-model="article.autor"
              type="text"
              required
              placeholder="Ex: João Silva"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            />
          </div>

          <!-- Ano -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Ano <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="article.ano"
              type="number"
              required
              min="1900"
              :max="currentYear"
              placeholder="Ex: 2024"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            />
          </div>
        </div>

        <!-- Resumo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center justify-between">
            <span>Resumo <span class="text-red-500">*</span></span>
            <span class="text-xs text-gray-500 font-normal">{{ article.resumo.length }} caracteres</span>
          </label>
          <textarea
            v-model="article.resumo"
            required
            rows="4"
            placeholder="Breve resumo do artigo (mínimo 50 caracteres)..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
          ></textarea>
          <p v-if="article.resumo.length > 0 && article.resumo.length < 50" class="text-xs text-amber-600 mt-1">
            ⚠️ O resumo deve ter pelo menos 50 caracteres
          </p>
        </div>

        <!-- Conteúdo -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center justify-between">
            <span>Conteúdo Completo <span class="text-red-500">*</span></span>
            <span class="text-xs text-gray-500 font-normal">{{ article.conteudo.length }} caracteres</span>
          </label>
          <textarea
            v-model="article.conteudo"
            required
            rows="10"
            placeholder="Texto completo do artigo (mínimo 100 caracteres)..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
          ></textarea>
          <p v-if="article.conteudo.length > 0 && article.conteudo.length < 100" class="text-xs text-amber-600 mt-1">
            ⚠️ O conteúdo deve ter pelo menos 100 caracteres
          </p>
        </div>

        <!-- Mensagem de erro -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
          <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-red-800 text-sm font-semibold">Erro ao adicionar artigo</p>
            <p class="text-red-700 text-sm mt-1">{{ error }}</p>
          </div>
        </div>

        <!-- Mensagem de sucesso -->
        <div v-if="success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
          <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-green-800 text-sm font-semibold">Artigo adicionado com sucesso!</p>
            <p class="text-green-700 text-sm mt-1">O artigo foi indexado no Elasticsearch.</p>
          </div>
        </div>

        <!-- Botões -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="loading"
            class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium disabled:opacity-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading || !isFormValid"
            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors font-medium flex items-center justify-center gap-2"
          >
            <div v-if="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ loading ? 'Salvando...' : 'Salvar Artigo' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddArticle',
  props: {
    show: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      article: {
        titulo: '',
        autor: '',
        ano: new Date().getFullYear(),
        resumo: '',
        conteudo: ''
      },
      loading: false,
      error: null,
      success: false,
      apiUrl: 'http://127.0.0.1:8000/api'
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
    isFormValid() {
      return (
        this.article.titulo.trim().length > 0 &&
        this.article.autor.trim().length > 0 &&
        this.article.ano >= 1900 &&
        this.article.ano <= this.currentYear &&
        this.article.resumo.trim().length >= 50 &&
        this.article.conteudo.trim().length >= 100
      );
    }
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.resetForm();
      }
    }
  },
  methods: {
    async submitArticle() {
      if (!this.isFormValid) {
        this.error = 'Por favor, preencha todos os campos corretamente.';
        return;
      }

      this.loading = true;
      this.error = null;
      this.success = false;

      try {
        const response = await fetch(`${this.apiUrl}/artigos`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.article)
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || data.error || 'Erro ao adicionar artigo');
        }

        this.success = true;

        // Aguarda 1.5s e emite evento de sucesso
        setTimeout(() => {
          this.$emit('article-added', data);
          this.$emit('close');
        }, 1500);

      } catch (err) {
        console.error('Erro ao adicionar artigo:', err);
        this.error = err.message || 'Erro ao adicionar artigo. Verifique sua conexão e tente novamente.';
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.article = {
        titulo: '',
        autor: '',
        ano: new Date().getFullYear(),
        resumo: '',
        conteudo: ''
      };
      this.error = null;
      this.success = false;
      this.loading = false;
    }
  }
};
</script>

<style scoped>
@keyframes modal {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.animate-modal {
  animation: modal 0.2s ease-out;
}

textarea {
  font-family: inherit;
}

/* Estilo para scroll suave */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f7fafc;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #a0aec0;
}
</style>

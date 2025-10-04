<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <div class="bg-white rounded-lg shadow-md p-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="bg-green-100 p-2 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900">Adicionar Novo Artigo</h2>
        </div>

        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Título -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Título do Artigo *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="Ex: Inteligência Artificial na Medicina Moderna"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">Digite um título claro e descritivo</p>
          </div>

          <!-- Autor -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Autor(es) *
            </label>
            <input
              v-model="form.autor"
              type="text"
              required
              placeholder="Ex: Dr. João Silva, Dra. Maria Santos"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <p class="text-xs text-gray-500 mt-1">Separe múltiplos autores com vírgula</p>
          </div>

          <!-- Ano -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Ano de Publicação *
            </label>
            <input
              v-model.number="form.ano"
              type="number"
              required
              min="1900"
              :max="currentYear"
              placeholder="Ex: 2024"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <!-- Resumo -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Resumo *
            </label>
            <textarea
              v-model="form.resumo"
              required
              rows="4"
              placeholder="Breve resumo do artigo (máximo 500 caracteres)"
              maxlength="500"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            ></textarea>
            <div class="flex justify-between items-center mt-1">
              <p class="text-xs text-gray-500">Um resumo conciso do conteúdo principal</p>
              <p :class="[
                'text-xs font-medium',
                form.resumo.length > 450 ? 'text-red-600' : 'text-gray-500'
              ]">
                {{ form.resumo.length }}/500
              </p>
            </div>
          </div>

          <!-- Conteúdo -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Conteúdo Completo *
            </label>
            <textarea
              v-model="form.conteudo"
              required
              rows="12"
              placeholder="Digite ou cole o conteúdo completo do artigo aqui..."
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none font-mono text-sm"
            ></textarea>
            <div class="flex justify-between items-center mt-1">
              <p class="text-xs text-gray-500">Conteúdo usado para busca semântica e resposta da IA</p>
              <p class="text-xs text-gray-500">{{ formatBytes(form.conteudo.length) }}</p>
            </div>
          </div>

          <!-- Mensagem de Sucesso/Erro -->
          <transition name="fade">
            <div v-if="message.text" :class="[
              'p-4 rounded-lg flex items-start gap-3',
              message.type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'
            ]">
              <svg 
                v-if="message.type === 'success'"
                class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg 
                v-else
                class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p :class="message.type === 'success' ? 'text-green-800' : 'text-red-800'">
                {{ message.text }}
              </p>
            </div>
          </transition>

          <!-- Botões -->
          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium flex items-center justify-center gap-2 shadow-sm"
            >
              <div v-if="loading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span>{{ loading ? 'Salvando...' : 'Salvar Artigo' }}</span>
            </button>
            
            <button
              type="button"
              @click="resetForm"
              :disabled="loading"
              class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium disabled:opacity-50"
            >
              Limpar
            </button>
          </div>
        </form>

        <!-- Exemplo de Artigo -->
        <div class="mt-8 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
              <p class="text-sm text-blue-800 mb-2 font-semibold">💡 Precisa de ajuda para começar?</p>
              <button
                @click="loadExample"
                type="button"
                class="text-sm text-blue-600 hover:text-blue-700 underline font-medium"
              >
                Clique aqui para carregar um artigo de exemplo
              </button>
            </div>
          </div>
        </div>

        <!-- Estatísticas -->
        <div class="mt-6 grid grid-cols-3 gap-4">
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ form.titulo.split(' ').length }}</p>
            <p class="text-xs text-gray-600 mt-1">Palavras no título</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ form.resumo.split(' ').filter(w => w).length }}</p>
            <p class="text-xs text-gray-600 mt-1">Palavras no resumo</p>
          </div>
          <div class="bg-gray-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ form.conteudo.split(' ').filter(w => w).length }}</p>
            <p class="text-xs text-gray-600 mt-1">Palavras no conteúdo</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddArtigo',
  data() {
    return {
      form: {
        titulo: '',
        autor: '',
        ano: new Date().getFullYear(),
        resumo: '',
        conteudo: ''
      },
      loading: false,
      message: {
        text: '',
        type: '' // 'success' ou 'error'
      },
      apiUrl: 'http://127.0.0.1:8000/api'
    };
  },
  computed: {
    currentYear() {
      return new Date().getFullYear();
    }
  },
  methods: {
    async submitForm() {
      this.loading = true;
      this.message = { text: '', type: '' };

      try {
        const response = await fetch(`${this.apiUrl}/artigos`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.form)
        });

        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));
          throw new Error(errorData.message || `Erro HTTP: ${response.status}`);
        }

        const data = await response.json();
        
        this.message = {
          text: '✅ Artigo adicionado com sucesso! O artigo já está disponível para busca.',
          type: 'success'
        };

        // Resetar formulário após 2 segundos
        setTimeout(() => {
          this.resetForm();
        }, 2000);

        // Emitir evento para componente pai
        this.$emit('artigo-criado', data);

      } catch (error) {
        console.error('Erro ao salvar artigo:', error);
        this.message = {
          text: `❌ Erro ao salvar artigo: ${error.message}. Verifique se o Laravel está rodando.`,
          type: 'error'
        };
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.form = {
        titulo: '',
        autor: '',
        ano: new Date().getFullYear(),
        resumo: '',
        conteudo: ''
      };
      this.message = { text: '', type: '' };
    },

    loadExample() {
      this.form = {
        titulo: 'Inteligência Artificial Aplicada ao Diagnóstico Médico: Uma Revisão Sistemática',
        autor: 'Dr. João Silva, Dra. Maria Santos, Prof. Carlos Oliveira',
        ano: 2024,
        resumo: 'Este estudo apresenta uma revisão sistemática sobre o uso de técnicas de inteligência artificial, especialmente deep learning, no diagnóstico médico. Analisamos 127 estudos publicados entre 2020-2024, demonstrando precisão média de 94.3% na detecção de doenças através de análise de imagens médicas. Os resultados indicam que a IA pode ser uma ferramenta valiosa para apoiar profissionais de saúde na tomada de decisões clínicas.',
        conteudo: `INTRODUÇÃO

A inteligência artificial (IA) tem revolucionado diversos setores da sociedade, e a área da saúde não é exceção. Nos últimos anos, observamos um crescimento exponencial no uso de técnicas de machine learning e deep learning para auxiliar no diagnóstico médico, especialmente através da análise de imagens como radiografias, tomografias e ressonâncias magnéticas.

METODOLOGIA

Esta revisão sistemática analisou 127 estudos publicados entre 2020 e 2024, selecionados de bases de dados como PubMed, IEEE Xplore e ACM Digital Library. Os critérios de inclusão foram: estudos que utilizaram técnicas de IA para diagnóstico médico, com validação clínica em pelo menos 1000 pacientes.

RESULTADOS

Os modelos de Redes Neurais Convolucionais (CNN) demonstraram precisão média de 94.3% na detecção de anomalias em imagens médicas. Especificamente:

- Detecção de pneumonia em radiografias torácicas: 96.1% de precisão
- Identificação de tumores em mamografias: 93.7% de precisão
- Diagnóstico de retinopatia diabética: 95.5% de precisão
- Detecção de COVID-19 em tomografias: 92.8% de precisão

DISCUSSÃO

Os resultados indicam que a IA pode ser uma ferramenta extremamente valiosa para apoiar profissionais de saúde. No entanto, é crucial enfatizar que estes sistemas devem ser usados como ferramentas de apoio à decisão, não como substituição do julgamento clínico humano.

Vantagens identificadas:
1. Redução do tempo de diagnóstico de horas para minutos
2. Identificação de padrões sutis invisíveis ao olho humano
3. Análise objetiva e consistente de grandes volumes de exames
4. Potencial de democratização do acesso a diagnósticos de qualidade

Desafios e limitações:
1. Necessidade de grandes datasets para treinamento
2. Questões éticas sobre responsabilidade em caso de erros
3. Viés algorítmico devido a datasets não representativos
4. Resistência de alguns profissionais à adoção da tecnologia

CONCLUSÃO

A inteligência artificial representa uma mudança de paradigma no diagnóstico médico. Com precisão superior a 94% em diversas aplicações, a IA tem potencial para salvar milhares de vidas através da detecção precoce de doenças. Estudos futuros devem focar na integração ética e responsável destas tecnologias no fluxo de trabalho clínico, garantindo que a tecnologia complemente, e não substitua, a expertise humana.

REFERÊNCIAS

[1] Smith, J. et al. (2023). Deep Learning for Medical Image Analysis. Nature Medicine.
[2] Santos, M. et al. (2024). CNN-based Diagnosis Systems: A Meta-Analysis. The Lancet Digital Health.
[3] Oliveira, C. et al. (2023). Ethics in AI-Assisted Medical Diagnosis. Journal of Medical Ethics.

AGRADECIMENTOS

Este trabalho foi financiado pelo Conselho Nacional de Desenvolvimento Científico e Tecnológico (CNPq) e pela Fundação de Amparo à Pesquisa do Estado de São Paulo (FAPESP).`
      };
    },

    formatBytes(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
  }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Animação suave para inputs */
input:focus, textarea:focus {
  transform: scale(1.01);
  transition: transform 0.2s ease;
}

/* Estilo para textarea com fonte monospace */
textarea.font-mono {
  font-family: 'Courier New', Courier, monospace;
  line-height: 1.6;
}
</style>
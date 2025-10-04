<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 rounded-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div>
             
                <h1 class="text-lg font-bold text-gray-900">Academic RAG</h1>

                <p class="text-xs text-gray-500">Elastic + Gemini AI</p>
      
            </div>
          </div>

          <!-- Menu Items -->
          <div class="flex gap-1">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="currentTab = tab.id"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition-colors',
                currentTab === tab.id
                  ? 'bg-blue-100 text-blue-700'
                  : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              {{ tab.icon }} {{ tab.label }}
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="py-6">
      <!-- Tab: Chat com IA -->
      <component 
        :is="currentComponent"
        @open-chat="openChatWithQuery"
        @artigo-criado="handleArtigoCreated"
      />
    </main>


    <!-- Notification Toast -->
    <transition name="slide-up">
      <div
        v-if="notification.show"
        :class="[
          'fixed bottom-6 right-6 px-6 py-4 rounded-lg shadow-lg max-w-md',
          notification.type === 'success' ? 'bg-green-600' : 'bg-red-600'
        ]"
      >
        <p class="text-white font-medium">{{ notification.message }}</p>
      </div>
    </transition>
  </div>
</template>

<script>
import ChatRAG from './components/ChatRAG.vue';
import ArtigosList from './components/ArtigosList.vue';
import AddArtigo from './components/AddArtigo.vue';

export default {
  name: 'App',
  components: {
    ChatRAG,
    ArtigosList,
    AddArtigo
  },
  data() {
    return {
      currentTab: 'chat',
      tabs: [
        { id: 'chat', label: 'Chat IA', icon: '💬' },
        { id: 'artigos', label: 'Artigos', icon: '📚' },
        { id: 'adicionar', label: 'Adicionar', icon: '➕' }
      ],
      notification: {
        show: false,
        message: '',
        type: 'success'
      }
    };
  },
  computed: {
    currentComponent() {
      const components = {
        chat: 'ChatRAG',
        artigos: 'ArtigosList',
        adicionar: 'AddArtigo'
      };
      return components[this.currentTab];
    }
  },
  methods: {
    openChatWithQuery(query) {
      this.currentTab = 'chat';
      // Passar query para o componente de chat
      this.$nextTick(() => {
        // Implementar lógica para passar query
      });
    },

    handleArtigoCreated() {
      this.showNotification('Artigo adicionado com sucesso!', 'success');
      // Opcionalmente, mudar para a tab de artigos
      setTimeout(() => {
        this.currentTab = 'artigos';
      }, 1500);
    },

    showNotification(message, type = 'success') {
      this.notification = { show: true, message, type };
      setTimeout(() => {
        this.notification.show = false;
      }, 3000);
    }
  }
};
</script>

<style>
/* Reset básico */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Animação de notificação */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from {
  transform: translateY(100px);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(100px);
  opacity: 0;
}
</style>
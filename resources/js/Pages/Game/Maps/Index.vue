<template>
    <div class="min-h-screen bg-dark flex flex-col">
      <GameHeader 
        title="MAP" 
        subtitle="LIST"
        :show-back="true"
        :back-url="route('game.home')"
      >
        <template #actions>
          <Link 
            :href="route('maps.create')"
            class="bg-gradient-to-r from-pink-600/40 to-purple-600/40 
                   hover:from-pink-500/60 hover:to-purple-500/60 
                   text-white px-6 py-2 rounded-lg transition-all
                   font-game border border-pink-500/30
                   hover:scale-105 hover:shadow-[0_0_15px_rgba(236,72,153,0.3)]"
          >
            Crear Nuevo Mapa
          </Link>
        </template>
      </GameHeader>
  
      <!-- Main content -->
      <div class="flex-1 container mx-auto p-8">
        <div class="grid gap-4">
          <div v-for="map in mapsList" :key="map.id" 
               class="map-item group">
            <div class="flex items-center gap-6">
              <!-- Thumbnail/Preview -->
              <div class="w-24 h-24 bg-purple-900/30 rounded-lg border-2 border-purple-500/30
                          group-hover:border-pink-500/50 transition-all overflow-hidden relative">
                <div class="absolute inset-0 flex items-center justify-center">
                  <template v-if="map.image_path">
                    <img :src="'/storage/' + map.image_path" alt="Map Image" class="w-full h-full object-cover">
                  </template>
                  <template v-else>
                    <svg class="w-12 h-12 text-purple-500/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                  </template>
                </div>
              </div>
  
              <!-- Info -->
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <div>
                    <h2 class="text-2xl font-game text-white group-hover:text-pink-500 transition-colors">
                      {{ map.title }}
                    </h2>
                    <p class="text-gray-400">{{ map.artist }}</p>
                  </div>
                  <div class="flex items-center gap-4">
                    <span class="text-pink-400 font-mono flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                      {{ map.bpm }} BPM
                    </span>
                    <span class="difficulty-badge" :class="getDifficultyClass(map.difficulty)">
                      {{ map.difficulty }}
                    </span>
                  </div>
                </div>
  
                <div class="mt-4 flex justify-between items-center">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-purple-900/50 border border-purple-500/30
                               flex items-center justify-center overflow-hidden">
                      <!-- Avatar placeholder -->
                      <svg class="w-5 h-5 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <span class="text-gray-400">{{ map.user.name }}</span>
                  </div>
                  <div class="flex gap-4">
                    <Link 
                      :href="route('maps.show', map)"
                      class="map-action-button"
                    >
                      Ver Detalles
                    </Link>
                    <button v-if="map.user_id === $page.props.auth.user.id"
                            @click="deleteMap(map)"
                            class="map-action-button text-red-400 hover:text-red-300 
                                   hover:bg-red-500/20">
                      Eliminar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Loading y End States -->
        <div class="mt-8 text-center">
          <!-- Spinner mientras carga -->
          <div v-if="isLoading" 
               class="inline-block animate-spin rounded-full h-8 w-8 border-4 
                      border-purple-500 border-t-transparent">
          </div>
  
          <!-- Mensaje de fin -->
          <div v-if="(reachedEnd || response?.has_more === false) && mapsList.length > 0" 
               class="bg-purple-900/20 border border-purple-500/30 
                      rounded-lg p-6 text-center animate-fadeIn">
            <div class="text-purple-400 font-game text-xl mb-2">
              🎮 ¡Has llegado al final!
            </div>
            <p class="text-gray-400">
              Has visto todos los mapas disponibles ({{ mapsList.length }} en total)
            </p>
          </div>
  
          <!-- Mensaje si no hay mapas -->
          <div v-if="mapsList.length === 0" 
               class="bg-purple-900/20 border border-purple-500/30 
                      rounded-lg p-6 text-center">
            <div class="text-purple-400 font-game text-xl mb-2">
              ¡No hay mapas aún!
            </div>
            <p class="text-gray-400 mb-4">
              Sé el primero en crear un mapa increíble
            </p>
            <Link 
              :href="route('maps.create')"
              class="inline-block bg-gradient-to-r from-pink-600/40 to-purple-600/40 
                     hover:from-pink-500/60 hover:to-purple-500/60 
                     text-white px-6 py-2 rounded-lg transition-all
                     font-game border border-pink-500/30
                     hover:scale-105 hover:shadow-[0_0_15px_rgba(236,72,153,0.3)]"
            >
              Crear Nuevo Mapa
            </Link>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue'
  import { Link } from '@inertiajs/vue3'
  import axios from 'axios'
  import GameHeader from '@/Components/GameHeader.vue'
  
  const props = defineProps({
    initialMaps: {
      type: Array,
      required: true
    }
  })
  
  const currentPage = ref(1)
  const isLoading = ref(false)
  const reachedEnd = ref(false)
  const mapsList = ref(props.initialMaps || [])
  const response = ref(null)
  
  function getDifficultyClass(difficulty) {
    const classes = {
      'EASY': 'bg-green-500/20 text-green-400 border-green-500/30',
      'NORMAL': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
      'HARD': 'bg-pink-500/20 text-pink-400 border-pink-500/30',
      'INSANE': 'bg-purple-500/20 text-purple-400 border-purple-500/30'
    }
    return classes[difficulty] || 'bg-gray-500/20'
  }
  
  function deleteMap(map) {
    if (confirm('¿Estás seguro de que quieres eliminar este mapa?')) {
      router.delete(route('maps.destroy', map))
    }
  }
  
  async function loadMoreMaps() {
    if (isLoading.value || reachedEnd.value) return
  
    try {
      isLoading.value = true
      const nextPage = currentPage.value + 1
  
      const result = await axios.get(`/maps`, {
        params: {
          page: nextPage
        }
      })
  
      response.value = result.data
  
      if (!result.data.has_more) {
        reachedEnd.value = true
      }
  
      if (result.data.data && result.data.data.length > 0) {
        mapsList.value = [...mapsList.value, ...result.data.data]
        currentPage.value = nextPage
      } else {
        reachedEnd.value = true
      }
  
    } catch (error) {
      console.error('Error cargando más mapas:', error)
    } finally {
      isLoading.value = false
    }
  }
  
  // Verificar inmediatamente si hay más páginas disponibles
  onMounted(async () => {
    window.addEventListener('scroll', handleScroll)
    
    // Hacer una petición inicial para verificar si hay más páginas
    try {
      const result = await axios.get(`/maps`, {
        params: { page: 1 }
      })
      response.value = result.data
      if (!result.data.has_more) {
        reachedEnd.value = true
      }
    } catch (error) {
      console.error('Error en la verificación inicial:', error)
    }
  })
  
  // Función para detectar scroll con debounce
  let scrollTimeout
  function handleScroll() {
    if (scrollTimeout) clearTimeout(scrollTimeout)
    
    scrollTimeout = setTimeout(() => {
      const scrollPosition = window.innerHeight + window.scrollY
      const documentHeight = document.documentElement.scrollHeight
      
      if (scrollPosition >= documentHeight - 100 && !isLoading.value && !reachedEnd.value) {
        loadMoreMaps()
      }
    }, 100)
  }
  
  onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    if (scrollTimeout) clearTimeout(scrollTimeout)
  })
  </script>
  
  <style scoped>
  .bg-dark {
    background-color: #0e0e0e;
    background-image: url('/imgs/default2.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
  }
  
  .map-item {
    @apply bg-purple-900/20 p-6 rounded-lg 
           border border-purple-500/40
           hover:bg-purple-900/30 
           hover:border-pink-500/50
           hover:shadow-[0_0_15px_rgba(168,85,247,0.2)]
           transition-all duration-300
           transform hover:scale-[1.01]
           animate-fadeIn;
  }
  
  .difficulty-badge {
    @apply px-3 py-1 rounded-full text-xs font-bold
           transition-all duration-300
           flex items-center gap-1
           border;
  }
  
  .difficulty-badge::before {
    content: '●';
    @apply text-xs;
  }
  
  .map-action-button {
    @apply px-4 py-1.5 rounded-lg
           bg-purple-900/30 border border-purple-500/30
           hover:bg-purple-900/50 hover:border-pink-500/50
           transition-all duration-300
           text-white hover:text-pink-500;
  }
  
  .animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
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
  
  /* Aplicar delay a cada item */
  .map-item:nth-child(1) { animation-delay: 0.1s; }
  .map-item:nth-child(2) { animation-delay: 0.15s; }
  .map-item:nth-child(3) { animation-delay: 0.2s; }
  .map-item:nth-child(4) { animation-delay: 0.25s; }
  .map-item:nth-child(5) { animation-delay: 0.3s; }
  
  /* Animaciones para los estados de carga y fin */
  .map-item {
    animation: fadeInUp 0.5s ease-out forwards;
    opacity: 0;
  }
  
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  /* Animación para el spinner */
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
  
  .animate-spin {
    animation: spin 1s linear infinite;
  }
  </style> 
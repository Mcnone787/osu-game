<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Header -->
    <GameHeader 
      title="BEAT" 
      subtitle="MASTER"
      :show-back="true"
      :back-url="route('game.home')"
    >
 
    </GameHeader>

    <!-- Main content -->
    <div class="flex-1 grid grid-cols-12 h-[calc(100vh-5rem)]">
      <!-- Ranking Panel (Izquierda) -->
      <div class="col-span-9 bg-black/80 p-8 text-white">
        <div v-if="noSongSelected" class="flex items-center justify-center h-full">
          <div class="text-center text-gray-400">
            <p class="text-2xl font-game mb-2">Selecciona una canción</p>
            <p class="text-sm">para ver sus rankings</p>
          </div>
        </div>
        
        <div v-else class="ranking-container">
          <div class="mb-6">
            <h2 class="text-2xl font-game mb-4">{{ selectedSong.title }}</h2>
            <DropdownSelector
              v-model="selectedRanking"
              :options="rankingTypes"
            />
          </div>
          <div class="ranking-list space-y-3">
            <div v-for="rank in currentRankings" :key="rank.position" 
                 class="ranking-item group">
              <div class="flex items-center gap-6">
                <span class="rank-number border-2 rounded-full w-10 h-10 
                           flex items-center justify-center text-2xl font-bold font-game"
                      :class="getRankClass(rank.position)">
                  #{{ rank.position }}
                </span>
                <div class="relative">
                  <img :src="rank.avatar" :alt="rank.player" 
                       class="w-12 h-12 rounded-full border-2 border-purple-500">
                  <div class="absolute -bottom-1 -right-1 bg-purple-500 text-xs px-1.5 py-px rounded-full text-[10px]">
                    Lv.{{ rank.level }}
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="text-lg font-game mb-0.5">{{ rank.player }}</h3>
                  <div class="flex items-center gap-3">
                    <span class="text-base text-gray-300 font-mono">{{ rank.score.toLocaleString() }}</span>
                    <span class="text-pink-400 text-sm">x{{ rank.combo }}</span>
                  </div>

                </div>
                <div class="text-right">
                  <p class="text-xl font-bold text-gradient">{{ rank.accuracy }}%</p>
                  <p class="text-xs text-gray-400">{{ rank.date }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Song List (Derecha) -->
      <div class="col-span-3 bg-black border-l border-purple-900 overflow-hidden text-white">
        <div class="song-list h-full overflow-y-auto px-2">
          <div v-for="song in songsList" 
               :key="song.id" 
               @click="selectedSong = song"
               class="song-item"
               :class="{'active': selectedSong?.id === song.id}">
            <!-- Imagen de la canción -->
            <div class="song-thumbnail">
              <img :src="song.thumbnail" alt="" class="w-24 h-24 object-cover">
              <div class="song-duration">{{ song.length }}</div>
            </div>
            <!-- Información de la canción -->
            <div class="song-info">
              <h3 class="text-xl font-game mb-1 truncate">{{ song.title }}</h3>
              <p class="text-gray-400 mb-2 text-sm truncate">{{ song.artist }}</p>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <span class="difficulty-badge" :class="getDifficultyClass(song.difficulty)">
                    {{ song.difficulty }}
                  </span>
                  <span class="text-gray-400 text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ song.bpm }} BPM
                  </span>
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
            <div v-if="(reachedEnd || response?.has_more === false) && songsList.length > 0" 
                 class="bg-gradient-to-r from-pink-600/20 to-purple-600/20 
                        border border-pink-500/30 rounded-lg p-6 
                        text-center animate-fadeIn mx-2 mb-4">
              <div class="text-pink-400 font-game text-xl mb-2">
                🎵 ¡Fin del catálogo!
              </div>
              <p class="text-gray-400">
                Has descubierto todas nuestras {{ songsList.length }} melodías
              </p>
              <div class="mt-3 text-sm text-purple-400">
                Vuelve pronto para encontrar nuevas canciones
              </div>
            </div>

            <!-- No songs message -->
            <div v-if="songsList.length === 0" 
                 class="bg-purple-900/20 border border-purple-500/30 
                        rounded-lg p-6 text-center mx-2">
              <div class="text-purple-400 font-game text-xl mb-2">
                🎵 Biblioteca vacía
              </div>
              <p class="text-gray-400">
                No hay canciones disponibles en este momento
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import DropdownSelector from '@/Components/DropdownSelector.vue'
import GameHeader from '@/Components/GameHeader.vue'
import { GlobeAltIcon, UserGroupIcon, FlagIcon } from '@heroicons/vue/24/outline'
import { markRaw } from 'vue'

const props = defineProps({
    initialSongs: {
        type: Object,
        required: true
    },
    currentUser: {
        type: Object,
        default: () => ({
            name: 'Guest',
            guest: true
        })
    }
})

// Definir los tipos de ranking disponibles
const rankingTypes = [
    { id: 'global', name: 'Global Ranking', icon: markRaw(GlobeAltIcon) },
    { id: 'friends', name: 'Friends Ranking', icon: markRaw(UserGroupIcon) },
    { id: 'country', name: 'Country Ranking', icon: markRaw(FlagIcon) }
]

const currentPage = ref(1)
const isLoading = ref(false)
const reachedEnd = ref(false)
const songsList = ref(props.initialSongs.data || [])
const selectedSong = ref(null)
const selectedRanking = ref(rankingTypes[0])
const response = ref(null)

async function loadMoreSongs() {
    if (isLoading.value || reachedEnd.value) return

    try {
        isLoading.value = true
        const nextPage = currentPage.value + 1

        const result = await axios.get(route('game.play'), {
            params: { page: nextPage }
        })

        response.value = result.data

        if (!result.data.has_more) {
            reachedEnd.value = true
        }

        if (result.data.data && result.data.data.length > 0) {
            songsList.value = [...songsList.value, ...result.data.data]
            currentPage.value = nextPage
        } else {
            reachedEnd.value = true
        }

    } catch (error) {
        console.error('Error cargando más canciones:', error)
    } finally {
        isLoading.value = false
    }
}

// Computed properties
const currentRankings = computed(() => {
    if (!selectedSong.value?.rankings) return []
    return selectedSong.value.rankings[selectedRanking.value.id] || []
})

const noSongSelected = computed(() => !selectedSong.value)

// Función para detectar scroll con debounce
let scrollTimeout
function handleScroll(e) {
    if (scrollTimeout) clearTimeout(scrollTimeout)
    
    scrollTimeout = setTimeout(() => {
        const element = e.target
        const scrollPosition = element.scrollTop + element.clientHeight
        const scrollHeight = element.scrollHeight
        
        if (scrollPosition >= scrollHeight - 100 && !isLoading.value && !reachedEnd.value) {
            loadMoreSongs()
        }
    }, 100)
}

onMounted(async () => {
    const songListElement = document.querySelector('.song-list')
    if (songListElement) {
        songListElement.addEventListener('scroll', handleScroll)
    }
    
    // Hacer una petición inicial para verificar si hay más páginas
    try {
        const result = await axios.get(route('game.play'), {
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

onUnmounted(() => {
    const songListElement = document.querySelector('.song-list')
    if (songListElement) {
        songListElement.removeEventListener('scroll', handleScroll)
    }
    if (scrollTimeout) clearTimeout(scrollTimeout)
})

const getDifficultyClass = (difficulty) => {
    const classes = {
        'RAIN': 'bg-blue-500',
        'HARD': 'bg-pink-500',
        'INSANE': 'bg-purple-500'
    }
    return classes[difficulty] || 'bg-gray-500'
}

const getRankClass = (position) => {
    if (position === 1) return 'text-yellow-400 border-yellow-400'
    if (position === 2) return 'text-gray-400 border-gray-400'
    if (position === 3) return 'text-amber-600 border-amber-600'
    return 'text-white border-purple-500'
}

// Añade este console.log para depuración
console.log('Initial songs:', songsList.value)
</script>

<style scoped>
.bg-dark {
  background-color: #0e0e0e;
  background-image: url('/imgs/default2.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.song-list {
  @apply py-2;
  height: calc(100vh - 5rem); /* Altura total menos el header */
  scrollbar-width: thin;
  scrollbar-color: theme('colors.purple.500') theme('colors.purple.900');
}

/* Estilos para la scrollbar en webkit (Chrome, Safari, etc) */
.song-list::-webkit-scrollbar {
  width: 8px;
}

.song-list::-webkit-scrollbar-track {
  @apply bg-purple-900/30;
}

.song-list::-webkit-scrollbar-thumb {
  @apply bg-purple-500/50 rounded-full;
  border: 2px solid transparent;
  background-clip: content-box;
}

.song-list::-webkit-scrollbar-thumb:hover {
  @apply bg-purple-400/50;
}

.song-item {
  @apply flex gap-4 p-3 rounded-lg
         cursor-pointer relative
         border border-transparent
         transition-all duration-300;
}

.song-item:hover {
  @apply bg-purple-900/30 border-purple-500/30
         transform scale-[1.02];
}

.song-item.active {
  @apply bg-gradient-to-r from-pink-600/20 to-purple-600/20
         border-pink-500/50;
}

.song-thumbnail {
  @apply relative rounded-lg overflow-hidden
         border-2 border-purple-900/50
         transition-all duration-300;
}

.song-item:hover .song-thumbnail {
  @apply border-pink-500/50;
}

.song-duration {
  @apply absolute bottom-0 right-0
         bg-black/80 text-white
         px-2 py-0.5 text-xs
         rounded-tl-lg;
}

.song-info {
  @apply flex-1 min-w-0;
}

.difficulty-badge {
  @apply px-3 py-1 rounded-full text-xs font-bold
         transition-all duration-300
         flex items-center gap-1;
}

.difficulty-badge::before {
  content: '●';
  @apply text-xs;
}

/* Clases para las dificultades */
.difficulty-badge.bg-blue-500 {
  @apply bg-blue-500/20 text-blue-400
         border border-blue-500/30;
}

.difficulty-badge.bg-pink-500 {
  @apply bg-pink-500/20 text-pink-400
         border border-pink-500/30;
}

.difficulty-badge.bg-purple-500 {
  @apply bg-purple-500/20 text-purple-400
         border border-purple-500/30;
}

/* Animación de entrada para los items */
.song-item {
  animation: slideIn 0.3s ease-out forwards;
  opacity: 0;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Delay para cada item */
.song-item:nth-child(1) { animation-delay: 0.1s; }
.song-item:nth-child(2) { animation-delay: 0.15s; }
.song-item:nth-child(3) { animation-delay: 0.2s; }
.song-item:nth-child(4) { animation-delay: 0.25s; }
.song-item:nth-child(5) { animation-delay: 0.3s; }

.ranking-item {
  @apply bg-purple-900/20 rounded-lg p-4
         border border-purple-900/30
         transition-all duration-300;
}

.ranking-item:hover {
  @apply bg-purple-900/30 border-purple-500/50
         transform scale-[1.01]
         shadow-[0_0_15px_rgba(168,85,247,0.2)];
}

.text-gradient {
  @apply bg-clip-text text-transparent
         bg-gradient-to-r from-pink-500 to-purple-500;
}

/* Animación suave al cargar */
.ranking-item {
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0;
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
.ranking-item:nth-child(1) { animation-delay: 0.1s; }
.ranking-item:nth-child(2) { animation-delay: 0.2s; }
.ranking-item:nth-child(3) { animation-delay: 0.3s; }
.ranking-item:nth-child(4) { animation-delay: 0.4s; }
.ranking-item:nth-child(5) { animation-delay: 0.5s; }

/* Añadir animaciones del spinner */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Añadir animación fadeIn */
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
</style>


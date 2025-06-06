<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Header -->
    <GameHeader 
      title="BEAT" 
      subtitle="MASTER"
      :show-back="true"
      :back-url="route('game.home')"
    />

    <!-- Main content -->
    <div class="flex-1 grid grid-cols-12 h-[calc(100vh-5rem)]">
      <!-- Panel de Ranking (Izquierda) -->
      <div class="col-span-9 relative overflow-hidden z-[90]">
        <!-- Fondo con efecto parallax cuando hay imagen -->
        <div v-if="selectedSong?.have_image" 
             class="absolute inset-0 bg-cover bg-center bg-no-repeat blur-sm transform scale-105"
             :style="{ backgroundImage: `url(${selectedSong.image_path})` }">
          <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-black/50"></div>
        </div>

        <!-- Fondo por defecto cuando hay canción seleccionada pero sin imagen -->
        <div v-else-if="selectedSong" 
             class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black/30">
          <!-- Grid de SVGs de fondo (sin animación) -->
      
          <!-- Gradiente por encima de los SVGs -->
          <div class="absolute inset-0 bg-gradient-to-br from-purple-900/40 via-black/50 to-black/60"></div>
        </div>

        <!-- Fondo cuando no hay canción seleccionada (el que ya teníamos) -->
        <div v-else class="absolute inset-0 bg-gradient-to-br from-purple-900/20 to-black/30">
          <div class="absolute inset-0 flex items-center justify-center opacity-10">
            <svg class="w-96 h-96 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="1" 
                    d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
          </div>
        </div>

        <!-- Contenido del Ranking -->
        <div class="relative h-full flex flex-col">
          <!-- Estado sin canción seleccionada -->
          <div v-if="noSongSelected" 
               class="flex items-center justify-center h-full">
            <div class="text-center space-y-4 relative z-10">
              <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-purple-500/20 
                          flex items-center justify-center
                          border-2 border-purple-500/30
                          shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                <svg class="w-12 h-12 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
              </div>
              <p class="text-2xl font-game mb-2 text-white">Selecciona una canción</p>
              <p class="text-sm text-gray-400">para ver sus rankings</p>
            </div>
          </div>

          <!-- Contenido cuando hay canción seleccionada -->
          <div v-else class="h-full flex flex-col" :key="selectedSong.id">
            <!-- Cabecera de la canción con animación mejorada -->
            <div class="song-header p-8 bg-black/30 backdrop-blur-sm"
                 :class="{ 'has-image': selectedSong.have_image }">
              <div class="flex items-start gap-8 animate-fadeIn">
                <!-- Imagen con animación -->
                <div v-if="selectedSong.have_image" 
                     class="w-40 h-40 rounded-lg overflow-hidden border-2 border-purple-500/30 animate-scaleIn">
                  <img :src="selectedSong.image_path" 
                       :alt="selectedSong.title" 
                       class="w-full h-full object-cover">
                </div>
                <!-- Placeholder cuando no hay imagen -->
                <div v-else 
                     class="w-40 h-40 rounded-lg overflow-hidden 
                            border-2 border-purple-500/30 
                            bg-purple-900/30 
                            flex items-center justify-center">
                  <svg class="w-20 h-20 text-purple-500/50 animate-float" 
                       fill="none" 
                       viewBox="0 0 24 24" 
                       stroke="currentColor">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                  </svg>
                </div>
                
                <!-- Información con animación -->
                <div class="flex-1 animate-slideInRight">
                  <h2 class="text-4xl font-game mb-2 text-white">{{ selectedSong.title }}</h2>
                  <p class="text-xl text-gray-300 mb-4">{{ selectedSong.artist }}</p>
                  <div class="flex items-center gap-4">
                    <span class="difficulty-badge" :class="getDifficultyClass(selectedSong.difficulty)">
                      {{ selectedSong.difficulty }}
                    </span>
                    <span class="text-gray-400 text-sm flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                      {{ selectedSong.bpm }} BPM
                    </span>
                    <span class="text-gray-400 text-sm flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ formatDuration(selectedSong.duration) }}
                    </span>
                  </div>
                </div>
                
                <!-- Botón de jugar -->
                <button @click="startGame(selectedSong)" 
                        class="play-button group">
                  <span class="relative z-10 font-game tracking-wider text-sm">JUGAR</span>
                  <svg class="w-4 h-4 ml-1.5 relative z-10 group-hover:transform group-hover:translate-x-1 transition-all duration-300" 
                       fill="none" 
                       viewBox="0 0 24 24" 
                       stroke="currentColor">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Selector y lista con animaciones -->
            <div class="flex-1 p-8 overflow-hidden flex flex-col">
              <div class="mb-6 animate-slideInDown relative z-[9998]">
                <DropdownSelector
                  v-model="selectedRanking"
                  :options="rankingTypes"
                  class="ranking-selector"
                />
              </div>

              <!-- Lista de rankings con animaciones mejoradas -->
              <div class="flex-1 overflow-y-auto ranking-scroll">
                <div class="grid gap-3">
                  <TransitionGroup
                    name="ranking-list"
                    tag="div"
                    class="grid gap-3"
                  >
                    <div v-for="rank in currentRankings" 
                         :key="`${selectedSong.id}-${rank.position}`"
                         class="ranking-item">
                      <div class="flex items-center gap-6">
                        <span class="rank-number" :class="getRankClass(rank.position)">
                          #{{ rank.position }}
                        </span>
                        <div class="relative">
                          <img :src="rank.avatar" :alt="rank.player" 
                               class="w-12 h-12 rounded-full border-2 border-purple-500/50">
                          <div class="absolute -bottom-1 -right-1 bg-purple-500 
                                    text-xs px-1.5 py-px rounded-full text-[10px] text-white">
                            Lv.{{ rank.level }}
                          </div>
                        </div>
                        <div class="flex-1">
                          <h3 class="text-lg font-game mb-0.5 text-white">{{ rank.player }}</h3>
                          <div class="flex items-center gap-3">
                            <span class="score-text text-base font-mono text-white">
                              {{ rank.score.toLocaleString() }}
                            </span>
                            <span class="text-pink-400 text-sm">x{{ rank.combo }}</span>
                          </div>
                        </div>
                        <div class="text-right">
                          <p class="text-xl font-bold accuracy-gradient">{{ rank.accuracy }}%</p>
                          <p class="date-text text-xs text-gray-300">{{ rank.date }}</p>
                        </div>
                      </div>
                    </div>
                  </TransitionGroup>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de canciones (Derecha) -->
      <div class="col-span-3 bg-black/95 border-l border-purple-900/50 overflow-hidden">
        <div class="song-list h-full overflow-y-auto px-2">
          <div v-for="song in songsList" 
               :key="song.id" 
               @click="selectSong(song)"
               class="song-item"
               :class="{'active': selectedSong?.id === song.id}">
            <!-- Imagen de la canción -->
            <div class="song-thumbnail">
              <template v-if="song.have_image">
                <img :src="song.image_path" 
                     :alt="song.title" 
                     class="w-full h-full object-cover">
              </template>
              <template v-else>
                <div class="w-full h-full bg-purple-900/30 
                            flex items-center justify-center">
                  <svg class="w-12 h-12 text-purple-500/50 animate-float" 
                       fill="none" 
                       viewBox="0 0 24 24" 
                       stroke="currentColor">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                  </svg>
                </div>
              </template>
              <div class="song-duration text-white">{{ song.length }}</div>
            </div>
            <!-- Información de la canción -->
            <div class="song-info">
              <h3 class="text-xl font-game mb-1 truncate text-white">{{ song.title }}</h3>
              <p class="text-gray-300 mb-2 text-sm truncate">{{ song.artist }}</p>
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
                 class="inline-block animate-spin-custom rounded-full h-8 w-8 border-4 
                        border-t-purple-500 border-r-purple-500/50 border-b-purple-500/30 border-l-purple-500/10">
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
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
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
let musicid = ref(null)
const isChangingSong = ref(false)

async function loadMoreSongs() {
    if (isLoading.value || reachedEnd.value) return

    try {
        isLoading.value = true
        currentPage.value++

        const response = await axios.get(route('game.play'), {
            params: {
                page: currentPage.value
            },
            timeout: 30000 // Timeout de 30 segundos
        })

        const newSongs = response.data.data.filter(song => song !== null)
        
        if (newSongs.length > 0) {
            songsList.value = [...songsList.value, ...newSongs]
        }

        reachedEnd.value = !response.data.has_more || newSongs.length === 0
        
    } catch (error) {
        console.error('Error cargando más canciones:', error)
        // Mostrar notificación de error si existe el sistema de notificaciones
        if (error.response?.data?.message) {
            console.error('Error del servidor:', error.response.data.message)
        }
        // Revertir el incremento de página en caso de error
        currentPage.value--
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
const selectSong = async (song) => {
    if (!song || selectedSong.value?.id === song.id) return
    
    try {
        isChangingSong.value = true
        selectedSong.value = null
        
        await nextTick()
        selectedSong.value = song
        
    } catch (error) {
        console.error('Error al seleccionar la canción:', error)
    } finally {
        setTimeout(() => {
            isChangingSong.value = false
        }, 600)
    }
}
function startGame(song) {
    try {
        if (!song || !song.id) {
            console.error('Canción inválida')
            return
        }
        musicid.value = song.id
        router.visit(route('game.play.start', { map: song.id }))
    } catch (error) {
        console.error('Error al iniciar el juego:', error)
    }
}
// Función para detectar scroll con debounce
let scrollTimeout
function handleScroll(e) {
    if (scrollTimeout) clearTimeout(scrollTimeout)
    
    scrollTimeout = setTimeout(() => {
        const element = e.target
        const scrollPosition = element.scrollTop + element.clientHeight
        const scrollHeight = element.scrollHeight
        
        // Debug logs
        console.log('Scroll position:', scrollPosition)
        console.log('Scroll height:', scrollHeight)
        console.log('Is loading:', isLoading.value)
        console.log('Reached end:', reachedEnd.value)
        console.log('Current page:', currentPage.value)
        
        // Reducimos el umbral y verificamos que realmente podemos cargar más
        if (scrollPosition >= scrollHeight - 300 && !isLoading.value && !reachedEnd.value) {
            loadMoreSongs()
        }
    }, 100)
}

onMounted(() => {
    // Inicializar con los valores correctos del backend
    currentPage.value = props.initialSongs.current_page || 1
    reachedEnd.value = !props.initialSongs.has_more

    // Añadir el evento de scroll al elemento correcto
    const songListElement = document.querySelector('.song-list')
    if (songListElement) {
        songListElement.addEventListener('scroll', handleScroll)
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
        'FACIL': 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
        'EASY': 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
        'NORMAL': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
        'DIFICIL': 'bg-orange-500/20 text-orange-400 border-orange-500/30',
        'HARD': 'bg-orange-500/20 text-orange-400 border-orange-500/30',
        'EXPERTO': 'bg-red-500/20 text-red-400 border-red-500/30',
        'EXPERT': 'bg-red-500/20 text-red-400 border-red-500/30'
    }
    return classes[difficulty] || 'bg-gray-500/20 text-gray-400 border-gray-500/30';
}

const getRankClass = (position) => {
    if (position === 1) return 'text-yellow-400 border-yellow-400'
    if (position === 2) return 'text-gray-400 border-gray-400'
    if (position === 3) return 'text-amber-600 border-amber-600'
    return 'text-white border-purple-500'
}

// Añade este console.log para depuración
console.log('Initial songs:', songsList.value)

// Función para formatear la duración
const formatDuration = (seconds) => {
  console.log('Duración en segundos:', seconds);
  if (!seconds) return '0:00';
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = Math.floor(seconds % 60);
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

// Añadir watch para manejar errores en la carga inicial
watch(() => props.initialSongs, (newValue) => {
    if (newValue.error) {
        console.error('Error en la carga inicial:', newValue.error)
        songsList.value = []
        reachedEnd.value = true
    } else {
        songsList.value = newValue.data.filter(song => song !== null)
        currentPage.value = newValue.current_page || 1
        reachedEnd.value = !newValue.has_more
    }
}, { immediate: true })
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
  @apply relative w-24 h-24 rounded-lg overflow-hidden
         border-2 border-purple-900/50
         transition-all duration-300
         bg-gradient-to-br from-purple-900/30 to-black/30;
}

.song-item:hover .song-thumbnail {
  @apply border-pink-500/50;
}

.song-item.active .song-thumbnail {
  @apply border-pink-500/50 shadow-[0_0_15px_rgba(236,72,153,0.2)];
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
         border
         flex items-center gap-1;
}

.difficulty-badge::before {
  content: '●';
  @apply text-xs;
}

/* Efectos hover para cada dificultad */
.difficulty-badge.bg-emerald-500\/20:hover {
  @apply bg-emerald-500/30 border-emerald-500/50;
}

.difficulty-badge.bg-blue-500\/20:hover {
  @apply bg-blue-500/30 border-blue-500/50;
}

.difficulty-badge.bg-orange-500\/20:hover {
  @apply bg-orange-500/30 border-orange-500/50;
}

.difficulty-badge.bg-red-500\/20:hover {
  @apply bg-red-500/30 border-red-500/50;
}

/* Animaciones base */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Clases de animación */
.animate-fadeIn {
  animation: fadeIn 0.6s ease-out forwards;
}

.animate-slideInRight {
  animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-slideInDown {
  animation: slideInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-scaleIn {
  animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Animaciones para los items del ranking */
.ranking-list-enter-active,
.ranking-list-leave-active {
  transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.ranking-list-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.ranking-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* Estilos del ranking item */
.ranking-item {
  @apply bg-gradient-to-r from-black/60 to-black/50 
         backdrop-blur-md rounded-lg p-4
         border border-purple-900/40
         transition-all duration-300
         relative;
  animation: slideInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
}

.ranking-item:hover {
  @apply border-purple-500/60
         transform scale-[1.02] translate-x-1
         bg-gradient-to-r from-purple-900/40 to-purple-800/30
         shadow-[0_0_20px_rgba(168,85,247,0.15)];
}

/* Estilos específicos para los rankings top 3 */
.ranking-item:nth-child(1) {
  @apply bg-gradient-to-r from-yellow-900/40 to-black/50
         border-yellow-500/40;
  animation-delay: 0.1s;
}

.ranking-item:nth-child(1):hover {
  @apply border-yellow-400/60
         bg-gradient-to-r from-yellow-900/40 to-yellow-800/30
         shadow-[0_0_25px_rgba(234,179,8,0.15)];
}

.ranking-item:nth-child(2) {
  @apply bg-gradient-to-r from-gray-800/40 to-black/50
         border-gray-400/40;
  animation-delay: 0.2s;
}

.ranking-item:nth-child(2):hover {
  @apply border-gray-300/60
         bg-gradient-to-r from-gray-800/40 to-gray-700/30
         shadow-[0_0_20px_rgba(156,163,175,0.15)];
}

.ranking-item:nth-child(3) {
  @apply bg-gradient-to-r from-amber-900/40 to-black/50
         border-amber-600/40;
  animation-delay: 0.3s;
}

.ranking-item:nth-child(3):hover {
  @apply border-amber-500/60
         bg-gradient-to-r from-amber-900/40 to-amber-800/30
         shadow-[0_0_20px_rgba(217,119,6,0.15)];
}

.ranking-item:nth-child(4) { animation-delay: 0.4s; }
.ranking-item:nth-child(5) { animation-delay: 0.5s; }

/* Efecto de brillo en hover */
.ranking-item::after {
  content: '';
  @apply absolute inset-0 rounded-lg
         bg-gradient-to-r from-purple-500/5 to-pink-500/5
         opacity-0 transition-opacity duration-300;
}

.ranking-item:hover::after {
  @apply opacity-100;
}

.play-button {
  @apply flex items-center justify-center gap-1.5 
         px-5 py-2
         font-bold text-sm text-white
         relative overflow-hidden
         transition-all duration-300
         rounded-lg
         border border-pink-500/30
         hover:border-pink-400/50
         hover:scale-[1.02]
         active:scale-95
         shadow-[0_0_10px_rgba(236,72,153,0.1)];
  background: linear-gradient(
    165deg, 
    rgba(236,72,153,0.9) 0%,
    rgba(168,85,247,0.9) 100%
  );
}

/* Efecto de brillo */
.play-button::before {
  content: '';
  @apply absolute inset-0 
         opacity-0
         transition-all duration-500;
  background: linear-gradient(
    165deg,
    rgba(236,72,153,1) 0%,
    rgba(168,85,247,1) 100%
  );
}

.play-button:hover::before {
  @apply opacity-100;
}

/* Efecto de resplandor */
.play-button::after {
  content: '';
  @apply absolute -inset-0.5
         bg-gradient-to-r from-pink-500 via-purple-500 to-pink-500
         opacity-0
         blur-md
         transition-all duration-500;
}

.play-button:hover::after {
  @apply opacity-30;
}

.play-button:hover {
  @apply shadow-[0_0_15px_rgba(236,72,153,0.2)];
}

/* Animación de pulso suave */
@keyframes softPulse {
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 10px rgba(236,72,153,0.1);
  }
  50% {
    transform: scale(1.01);
    box-shadow: 0 0 12px rgba(236,72,153,0.15);
  }
}

.play-button {
  animation: softPulse 2s infinite ease-in-out;
}

.play-button:hover {
  animation: none;
}

.ranking-selector {
  @apply bg-black/40 backdrop-blur-sm rounded-lg 
         border border-purple-500/30
         relative z-50;
}

.ranking-scroll {
  @apply pr-4 -mr-4 relative z-10;
  scrollbar-width: thin;
  scrollbar-color: theme('colors.purple.500') theme('colors.purple.900/30');
}

.ranking-scroll::-webkit-scrollbar {
  @apply w-2;
}

.ranking-scroll::-webkit-scrollbar-track {
  @apply bg-purple-900/30 rounded-full;
}

.ranking-scroll::-webkit-scrollbar-thumb {
  @apply bg-purple-500/50 rounded-full hover:bg-purple-400/50;
}

.rank-number {
  @apply border-2 rounded-full w-10 h-10 
         flex items-center justify-center text-2xl font-bold font-game;
}

/* Animaciones para el cambio de mapa */
.song-header {
  animation: slideDown 0.5s ease-out forwards;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Gradiente para el porcentaje de accuracy */
.accuracy-gradient {
  @apply bg-clip-text text-transparent bg-gradient-to-r 
         from-pink-400 to-purple-400 
         drop-shadow-[0_0_2px_rgba(236,72,153,0.3)];
}

/* Ajustes para los textos */
.song-item h3 {
  @apply text-white drop-shadow-sm;
}

.song-item .song-info p {
  @apply text-gray-300;
}

.ranking-item h3 {
  @apply text-white font-semibold drop-shadow-sm;
}

.score-text {
  @apply text-white drop-shadow-sm;
}

.date-text {
  @apply text-gray-300;
}

/* Animación suave para el fondo */
.bg-cover {
  transition: background-image 0.3s ease-in-out;
}

/* Efecto de brillo para el icono circular */
.rounded-full {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 15px rgba(168, 85, 247, 0.2);
  }
  50% {
    box-shadow: 0 0 25px rgba(168, 85, 247, 0.4);
  }
}

/* Desfase de animación para cada icono */
.grid svg:nth-child(3n+1) { animation-delay: 0s; }
.grid svg:nth-child(3n+2) { animation-delay: 1s; }
.grid svg:nth-child(3n+3) { animation-delay: 2s; }

@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(5deg);
  }
}

/* Transición suave entre estados */
.absolute {
  transition: all 0.3s ease-in-out;
}

/* Animación flotante solo para los SVG de mapas sin imagen */
.animate-float {
  animation: float 3s infinite ease-in-out;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-5px) rotate(5deg);
  }
}

/* Ajuste del background para la información del mapa */
.song-header {
  position: relative;
}

.song-header::before {
  content: '';
  position: absolute;
  inset: 0;
  @apply bg-gradient-to-br from-purple-900/20 to-black/30;
  opacity: 0.1;
  z-index: -1;
}

/* Añadir imagen de fondo si existe */
.song-header.has-image::before {
  background-image: v-bind("selectedSong?.image_path ? `url(${selectedSong.image_path})` : ''");
  background-size: cover;
  background-position: center;
}

/* Transiciones suaves */
.song-header, .song-thumbnail {
  transition: all 0.3s ease-in-out;
}

/* Animación para el icono principal */
.animate-float {
  animation: float 3s infinite ease-in-out;
}

/* Animación para los iconos de fondo */
.animate-float-bg {
  animation: floatBg 6s infinite ease-in-out;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0) rotate(0deg);
  }
  50% {
    transform: translateY(-5px) rotate(5deg);
  }
}

@keyframes floatBg {
  0%, 100% {
    transform: translateY(0) rotate(0deg) scale(1);
  }
  50% {
    transform: translateY(-10px) rotate(5deg) scale(1.05);
  }
}

/* Asegurar que las transiciones sean suaves */
.absolute, .song-header {
  transition: all 0.3s ease-in-out;
}

/* Efecto de escala al entrar */
.animate-scaleIn {
  animation: scaleIn 0.3s ease-out forwards;
}

@keyframes scaleIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

/* Añadir estilos para estados de error */
.error-state {
    @apply text-red-400 bg-red-900/20 border-red-500/30;
}

/* Animación personalizada para el spinner */
@keyframes spin-custom {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin-custom {
    animation: spin-custom 1s linear infinite;
}
</style>


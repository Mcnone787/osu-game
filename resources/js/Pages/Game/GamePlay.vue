<template>
  <div class="game-container min-h-screen bg-black text-white">
    <!-- Overlay de cuenta regresiva -->
    <div v-if="showCountdown" 
         class="fixed inset-0 bg-black/90 flex items-center justify-center z-50">
      <div class="text-center">
        <div class="text-8xl font-game text-white animate-pulse">
          {{ countdown }}
        </div>
      </div>
    </div>

    <!-- HUD del juego -->
    <div class="game-hud fixed top-0 left-0 right-0 p-4 flex justify-between items-center">
      <!-- Info de la canción -->
      <div class="flex items-center gap-4">
        <button @click="exitGame" 
                class="bg-purple-900/30 hover:bg-purple-900/50 
                       text-white p-2 rounded-lg transition-all">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </button>
        <div>
          <h2 class="text-xl font-game">{{ map.title }}</h2>
          <p class="text-sm text-gray-400">{{ map.artist }}</p>
        </div>
      </div>

      <!-- Puntuación -->
      <div class="flex items-center gap-8">
        <div class="text-right">
          <div class="text-3xl font-mono font-bold">{{ score.toLocaleString() }}</div>
          <div class="text-sm text-gray-400">Puntuación</div>
        </div>
        <div class="text-right">
          <div class="text-3xl font-mono font-bold text-pink-400">x{{ combo }}</div>
          <div class="text-sm text-gray-400">Combo</div>
        </div>
        <div class="text-right">
          <div class="text-3xl font-mono font-bold text-purple-400">{{ accuracy.toFixed(2) }}%</div>
          <div class="text-sm text-gray-400">Precisión</div>
        </div>
      </div>
    </div>

    <!-- Área de juego -->
    <div class="game-area relative h-screen">
      <!-- Línea de tiempo -->
      <div class="timeline absolute bottom-32 left-0 right-0 h-1 bg-purple-500/30"></div>

      <!-- Carriles de notas -->
      <div class="note-lanes absolute bottom-32 left-1/2 -translate-x-1/2 
                  flex gap-4 items-end">
        <div v-for="lane in 4" :key="lane" 
             class="note-lane w-16 h-[70vh] relative">
          <!-- Receptor de notas -->
          <div class="note-receptor absolute bottom-0 w-full h-16 
                      bg-purple-500/20 rounded-lg border-2 border-purple-500/30
                      transition-all"
               :class="{ 'active': activeKeys[lane-1] }">
          </div>
        </div>
      </div>
    </div>

    <!-- Pantalla de resultados -->
    <div v-if="gameEnded" 
         class="fixed inset-0 bg-black/95 flex items-center justify-center z-50">
      <div class="text-center">
        <h2 class="text-4xl font-game mb-8">Resultados</h2>
        <div class="space-y-4 mb-8">
          <div class="text-6xl font-mono font-bold mb-2">{{ score.toLocaleString() }}</div>
          <div class="text-xl text-pink-400">Combo Máximo: x{{ maxCombo }}</div>
          <div class="text-xl text-purple-400">Precisión: {{ accuracy.toFixed(2) }}%</div>
        </div>
        <div class="flex justify-center gap-4">
          <button @click="restartGame" 
                  class="bg-purple-900/30 hover:bg-purple-900/50 
                         text-white px-6 py-2 rounded-lg transition-all">
            Reintentar
          </button>
          <button @click="exitGame" 
                  class="bg-pink-900/30 hover:bg-pink-900/50 
                         text-white px-6 py-2 rounded-lg transition-all">
            Salir
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  map: {
    type: Object,
    required: true
  }
})

// Estado del juego
const showCountdown = ref(true)
const countdown = ref(3)
const gameStarted = ref(false)
const gameEnded = ref(false)
const score = ref(0)
const combo = ref(0)
const maxCombo = ref(0)
const accuracy = ref(100)
const activeKeys = ref([false, false, false, false])

// Audio
const audioElement = ref(null)

// Iniciar el juego
function startGame() {
  showCountdown.value = true
  countdown.value = 3
  
  const countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value === 0) {
      clearInterval(countdownInterval)
      setTimeout(() => {
        showCountdown.value = false
        gameStarted.value = true
        // Iniciar la música y el juego
        audioElement.value = new Audio(props.map.audio_path)
        audioElement.value.play()
      }, 1000)
    }
  }, 1000)
}

// Manejar input del teclado
function handleKeyDown(event) {
  if (!gameStarted.value || gameEnded.value) return
  
  const keyMap = {
    'd': 0,
    'f': 1,
    'j': 2,
    'k': 3
  }
  
  if (keyMap[event.key] !== undefined) {
    activeKeys.value[keyMap[event.key]] = true
    // Aquí irá la lógica de golpear notas
  }
}

function handleKeyUp(event) {
  const keyMap = {
    'd': 0,
    'f': 1,
    'j': 2,
    'k': 3
  }
  
  if (keyMap[event.key] !== undefined) {
    activeKeys.value[keyMap[event.key]] = false
  }
}

// Salir del juego
function exitGame() {
  if (audioElement.value) {
    audioElement.value.pause()
    audioElement.value = null
  }
  router.get(route('game.play'))
}

// Reiniciar el juego
function restartGame() {
  score.value = 0
  combo.value = 0
  maxCombo.value = 0
  accuracy.value = 100
  gameEnded.value = false
  startGame()
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
  window.addEventListener('keyup', handleKeyUp)
  startGame()
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
  window.removeEventListener('keyup', handleKeyUp)
  if (audioElement.value) {
    audioElement.value.pause()
    audioElement.value = null
  }
})
</script>

<style scoped>
.game-container {
  background: linear-gradient(to bottom, #000000, #1a1a1a);
}

.note-receptor.active {
  @apply bg-purple-500/40 border-purple-500/60
         scale-105 shadow-[0_0_15px_rgba(168,85,247,0.3)];
}

/* Animación de pulso para la cuenta regresiva */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.5;
    transform: scale(0.95);
  }
}

.animate-pulse {
  animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style> 
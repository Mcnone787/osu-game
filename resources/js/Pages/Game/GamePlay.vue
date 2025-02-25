<template>
  <div class="game-container min-h-screen bg-black text-white relative overflow-hidden" style="background:transparent;">
    <!-- Video de fondo -->
    <video v-if="map.video_path"
           ref="videoBackground"
           class="video-background"
           loop
           muted
           playsinline
           @loadeddata="onVideoLoaded"
           @error="onVideoError">
      <source :src="map.video_path" type="video/mp4">
    </video>
    
    <!-- Overlay para el video -->
    <div class="video-overlay"></div>

    <!-- Contenido del juego -->
    <div class="relative z-10">
      <!-- Pantalla de inicio (z-index más alto) -->
      <div v-if="!gameInitialized" 
           class="fixed inset-0 bg-black/95 flex items-center justify-center z-[60]">
        <div class="text-center space-y-6">
          <h2 class="text-4xl font-game mb-4">{{ map.title }}</h2>
          <p class="text-xl text-gray-400">{{ map.artist }}</p>
          <div class="flex flex-col items-center gap-4 mt-8">
            <div class="text-sm text-gray-400 mb-2">Controles</div>
            <div class="flex gap-4">
              <div class="w-12 h-12 bg-purple-900/30 rounded-lg border border-purple-500/30 
                          flex items-center justify-center font-mono">D</div>
              <div class="w-12 h-12 bg-purple-900/30 rounded-lg border border-purple-500/30 
                          flex items-center justify-center font-mono">F</div>
              <div class="w-12 h-12 bg-purple-900/30 rounded-lg border border-purple-500/30 
                          flex items-center justify-center font-mono">J</div>
              <div class="w-12 h-12 bg-purple-900/30 rounded-lg border border-purple-500/30 
                          flex items-center justify-center font-mono">K</div>
            </div>
          </div>
          <button @click="initializeGame" 
                  class="mt-8 bg-gradient-to-r from-pink-600/40 to-purple-600/40 
                         hover:from-pink-500/60 hover:to-purple-500/60 
                         text-white px-8 py-3 rounded-lg transition-all
                         font-game text-xl border border-pink-500/30
                         hover:scale-105 hover:shadow-[0_0_15px_rgba(236,72,153,0.3)]">
            Presiona para comenzar
          </button>
        </div>
      </div>

      <!-- Overlay de cuenta regresiva (z-index más bajo) -->
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
        <!-- Feedback de hits en las posiciones de las flechas -->
        <div class="hit-feedback-container fixed inset-0 pointer-events-none z-40">
          <!-- Feedback izquierdo -->
          <div class="feedback-left fixed left-[15%] bottom-[30%] w-[30%]">
            <transition name="feedback">
              <div v-if="leftFeedback" 
                   :key="leftFeedback.id"
                   class="feedback-text absolute"
                   :class="leftFeedback.type">
                {{ leftFeedback.text }}
              </div>
            </transition>
          </div>

          <!-- Feedback derecho -->
          <div class="feedback-right fixed right-[15%] bottom-[30%] w-[30%]">
            <transition name="feedback">
              <div v-if="rightFeedback"
                   :key="rightFeedback.id"
                   class="feedback-text absolute"
                   :class="rightFeedback.type">
                {{ rightFeedback.text }}
              </div>
            </transition>
          </div>
        </div>

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
            
            <!-- Notas -->
            <div v-for="note in notes.filter(n => n.lane === lane-1 && !n.hit && !n.missed)" 
                 :key="note.timing"
                 class="absolute w-full h-16 bg-purple-500/40 rounded-lg border-2 
                        border-purple-500/30 transition-transform"
                 :style="{ bottom: `${note.y}px` }">
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
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  map: {
    type: Object,
    required: true
  }
})

const videoBackground = ref(null)

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
const keyPressTime = ref([0, 0, 0, 0]) // Nuevo: para controlar el tiempo entre pulsaciones
const gameInitialized = ref(false)
const imagePath = ref(null)
const thumbnailPath = ref(null)
const videoPath = ref(null)
// Audio y notas
const audioElement = ref(null)
const notes = ref([])
const currentTime = ref(0)
const gameLoop = ref(null)

// Cambiar a un solo feedback por lado
const leftFeedback = ref(null)
const rightFeedback = ref(null)
let feedbackId = 0
let leftTimeoutId = null
let rightTimeoutId = null  // Guardamos las referencias de los timeouts

// Configuración del juego
const SCROLL_SPEED = 400 // píxeles por segundo
const PERFECT_WINDOW = 80 // ms (aumentado para más tolerancia)
const GOOD_WINDOW = 200 // ms (aumentado para más tolerancia)
const MISS_WINDOW =300 // ms
const NOTE_OFFSET = 4000 // ms

// Textos aleatorios para cada tipo de feedback
const feedbackTexts = {
  perfect: ['PERFECT!', '¡INCREÍBLE!', '¡EXCELENTE!', '¡PERFECTO!', '¡ASOMBROSO!'],
  good: ['¡BIEN!', '¡GENIAL!', 'GOOD!', '¡SIGUE ASÍ!', '¡CONTINÚA!'],
  miss: ['¡MISS!', '¡OH NO!', '¡CASI!', '¡SIGUIENTE!', '¡CONCENTRATE!']
}

// Inicializar notas
function initializeNotes() {
  if (!Array.isArray(props.map.notes)) {
    console.warn('No hay notas disponibles para este mapa');
    console.log('Notas recibidas:', props.map.notes);
    notes.value = [];
    return;
  }

  console.log('Notas originales:', props.map.notes);
  
  // Convertir el tiempo a milisegundos y añadir offset
  notes.value = props.map.notes.map(note => ({
    id: note.id,
    lane: parseInt(note.lane) || 0,
    timing: (parseFloat(note.time) * 1000) + NOTE_OFFSET, // Añadir offset
    y: -100,
    hit: false,
    missed: false
  }));

  // Ordenar las notas por tiempo
  notes.value.sort((a, b) => a.timing - b.timing);

  console.log('Notas procesadas:', notes.value);
}

// Actualizar posición de las notas
function updateNotes() {
  if (!audioElement.value) return;
  
  const currentAudioTime = (audioElement.value.currentTime * 1000) + NOTE_OFFSET;
  currentTime.value = currentAudioTime;
  
  console.log('Update time:', {
    audioTime: audioElement.value.currentTime * 1000,
    withOffset: currentAudioTime,
    activeNotes: notes.value.filter(n => !n.hit && !n.missed).length
  });
  
  notes.value.forEach(note => {
    if (!note.hit && !note.missed) {
      const timeUntilHit = note.timing - currentTime.value;
      note.y = (timeUntilHit / 1000) * SCROLL_SPEED;
      
      // Debug para notas cerca del punto de golpe
      if (Math.abs(timeUntilHit) < MISS_WINDOW) {
        console.log('Nota cerca:', {
          lane: note.lane,
          timeUntilHit,
          y: note.y,
          timing: note.timing
        });
      }
      
      // Solo marcar como perdida si realmente pasó demasiado tiempo
      if (timeUntilHit < -MISS_WINDOW && note.y < -50) {
        note.missed = true;
        combo.value = 0;
        console.log('Nota perdida:', {
          timeUntilHit,
          y: note.y,
          note
        });
      }
    }
  });
}

// Función para obtener texto aleatorio
const getRandomText = (type) => {
  const texts = feedbackTexts[type]
  return texts[Math.floor(Math.random() * texts.length)]
}

// Función modificada para mostrar feedback
const showHitFeedback = (type, lane) => {
  console.log('Mostrando feedback:', { type, lane, feedbackId })

  const feedback = {
    id: feedbackId++,
    type,
    text: getRandomText(type)
  }
  
  if (lane < 2) {
    // Limpiar timeout anterior si existe
    if (leftTimeoutId) {
      clearTimeout(leftTimeoutId)
      console.log('Limpiando timeout izquierdo anterior')
    }
    
    console.log('Estado anterior izquierdo:', leftFeedback.value)
    leftFeedback.value = null
    
    // Pequeño delay para asegurar la limpieza
    setTimeout(() => {
      leftFeedback.value = feedback
      console.log('Nuevo feedback izquierdo:', feedback)
      
      leftTimeoutId = setTimeout(() => {
        if (leftFeedback.value?.id === feedback.id) {
          leftFeedback.value = null
          console.log('Limpiando feedback izquierdo:', feedback.id)
        }
      }, 300)
    }, 16) // Un frame aproximadamente
  } else {
    // Limpiar timeout anterior si existe
    if (rightTimeoutId) {
      clearTimeout(rightTimeoutId)
      console.log('Limpiando timeout derecho anterior')
    }
    
    console.log('Estado anterior derecho:', rightFeedback.value)
    rightFeedback.value = null
    
    // Pequeño delay para asegurar la limpieza
    setTimeout(() => {
      rightFeedback.value = feedback
      console.log('Nuevo feedback derecho:', feedback)
      
      rightTimeoutId = setTimeout(() => {
        if (rightFeedback.value?.id === feedback.id) {
          rightFeedback.value = null
          console.log('Limpiando feedback derecho:', feedback.id)
        }
      }, 300)
    }, 16)
  }
}

// Manejar golpes de notas
const handleKeyDown = (event) => {
  if (!gameStarted.value || showCountdown.value) return // Cambiado gameInitialized por gameStarted
  
  console.log('Tecla presionada:', event.key) // Debug para ver si se detecta la tecla

  // Mapeo de teclas a carriles
  const keyToLane = {
    'd': 0,
    'f': 1,
    'j': 2,
    'k': 3
  }

  const lane = keyToLane[event.key.toLowerCase()]
  if (lane === undefined) return

  // Marcar el carril como activo
  activeKeys.value[lane] = true
  keyPressTime.value[lane] = Date.now()

  // Obtener el tiempo actual correctamente
  const currentTime = audioElement.value ? 
    (audioElement.value.currentTime * 1000) + NOTE_OFFSET : // Usar NOTE_OFFSET en lugar de offset.value
    0

  console.log('Tiempo actual:', currentTime) // Debug

  // Encontrar la nota más cercana en este carril
  const nearestNote = notes.value.find(note => 
    note.lane === lane && 
    !note.hit &&
    !note.missed &&
    Math.abs(currentTime - note.timing) <= GOOD_WINDOW
  )

  if (nearestNote) {
    const timeDiff = Math.abs(currentTime - nearestNote.timing)
    
    console.log('Nota encontrada:', {
      lane,
      currentTime,
      noteTiming: nearestNote.timing,
      timeDiff
    })

    // Evaluar la precisión del hit
    if (timeDiff <= PERFECT_WINDOW) {
      nearestNote.hit = true
      score.value += 100
      combo.value++
      maxCombo.value = Math.max(maxCombo.value, combo.value)
      showHitFeedback('perfect', nearestNote.lane)
    } else if (timeDiff <= GOOD_WINDOW) {
      nearestNote.hit = true
      score.value += 50
      combo.value++
      maxCombo.value = Math.max(maxCombo.value, combo.value)
      showHitFeedback('good', nearestNote.lane)
    }
  } else {
    // Feedback para miss
    combo.value = 0
    showHitFeedback('miss', lane)
  }
}

const handleKeyUp = (event) => {
  const keyToLane = {
    'd': 0,
    'f': 1,
    'j': 2,
    'k': 3
  }

  const lane = keyToLane[event.key.toLowerCase()]
  if (lane !== undefined) {
    activeKeys.value[lane] = false
  }
}

// Función para manejar el video de fondo
const handleBackgroundVideo = async () => {
  if (videoBackground.value && props.map.video_path) {
    try {
      await videoBackground.value.play()
      videoBackground.value.volume = 0
    } catch (error) {
      console.error('Error al reproducir el video:', error)
    }
  }
}

// Funciones de debug para el video
const onVideoLoaded = () => {
  console.log('Video cargado:', {
    duration: videoBackground.value?.duration,
    videoPath: props.map.video_path,
    readyState: videoBackground.value?.readyState
  })
}

const onVideoError = (error) => {
  console.error('Error en el video:', error)
}

// Función modificada para iniciar el juego
function startGame() {
  showCountdown.value = true
  countdown.value = 3
  
  console.log('Iniciando juego, video path:', props.map.video_path)
  
  // Asegurarse de que el video esté pausado inicialmente
  if (videoBackground.value) {
    console.log('Preparando video')
    videoBackground.value.currentTime = 0
    videoBackground.value.pause()
  }
  
  const countdownInterval = setInterval(() => {
    countdown.value--
    console.log('Cuenta regresiva:', countdown.value)
    
    if (countdown.value === 0) {
      clearInterval(countdownInterval)
      setTimeout(() => {
        showCountdown.value = false
        gameStarted.value = true
        
        // Iniciar el video cuando termina la cuenta regresiva
        if (videoBackground.value && props.map.video_path) {
          console.log('Intentando reproducir video')
          videoBackground.value.load() // Forzar recarga del video
          const playPromise = videoBackground.value.play()
          
          if (playPromise !== undefined) {
            playPromise
              .then(() => {
                videoBackground.value.volume = 0
                console.log('Video reproduciendo correctamente')
              })
              .catch(error => {
                console.error('Error al reproducir el video:', error)
              })
          }
        } else {
          console.log('No se encontró video o referencia:', {
            video: videoBackground.value,
            path: props.map.video_path
          })
        }
        
        // Iniciar el audio y el juego
        audioElement.value.play()
          .then(() => {
            console.log('Audio iniciado correctamente')
            gameLoop.value = setInterval(updateNotes, 16)
          })
          .catch(error => {
            console.error('Error al reproducir el audio:', error)
          })
      }, 1000)
    }
  }, 1000)
}

// Terminar el juego
function endGame() {
  gameEnded.value = true
  gameStarted.value = false
  if (gameLoop.value) {
    clearInterval(gameLoop.value)
  }
  // Calcular precisión final
  const totalNotes = notes.value.length
  const hitNotes = notes.value.filter(note => note.hit).length
  accuracy.value = (hitNotes / totalNotes) * 100
}

// Salir del juego
function exitGame() {
  if (audioElement.value) {
    audioElement.value.pause()
    audioElement.value = null
  }
  if (gameLoop.value) {
    clearInterval(gameLoop.value)
  }
  router.get(route('game.play'))
}

// Reiniciar el juego
function restartGame() {
  if (audioElement.value) {
    audioElement.value.pause()
    audioElement.value = null
  }
  if (gameLoop.value) {
    clearInterval(gameLoop.value)
  }
  score.value = 0
  combo.value = 0
  maxCombo.value = 0
  accuracy.value = 100
  gameEnded.value = false
  notes.value = []
  startGame()
}

// Función para inicializar el juego
function initializeGame() {
  gameInitialized.value = true
  audioElement.value = new Audio(props.map.audio_path)
  audioElement.value.load() // Precarga el audio
  startGame()
}

onMounted(() => {
  console.log('Componente montado, verificando video:', {
    ref: videoBackground.value,
    path: props.map.video_path
  })
  handleBackgroundVideo()
  window.addEventListener('keydown', handleKeyDown)
  window.addEventListener('keyup', handleKeyUp)
})  

// Detener el video cuando el componente se desmonta
onUnmounted(() => {
  if (videoBackground.value) {
    videoBackground.value.pause()
  }
  window.removeEventListener('keydown', handleKeyDown)
  window.removeEventListener('keyup', handleKeyUp)
  if (audioElement.value) {
    audioElement.value.pause()
    audioElement.value = null
  }
  if (gameLoop.value) {
    clearInterval(gameLoop.value)
  }
  if (leftTimeoutId) clearTimeout(leftTimeoutId)
  if (rightTimeoutId) clearTimeout(rightTimeoutId)
})

// Observar cambios en el mapa para reiniciar el video si es necesario
watch(() => props.map, async (newMap) => {
  await nextTick()
  handleBackgroundVideo()
})
</script>

<style scoped>
.game-container {
  @apply relative min-h-screen;
}

/* Estilos mejorados para el video de fondo */
.video-background {
  position: fixed;
  right: 0;
  bottom: 0;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  z-index: -2;
  transform: translateX(0) translateY(0);
  object-fit: cover;
  opacity: 0.4;
  transition: opacity 0.7s ease;
}

/* Overlay para mejorar la visibilidad del contenido */
.video-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.7),
    rgba(0, 0, 0, 0.5),
    rgba(0, 0, 0, 0.7)
  );
  z-index: -1;
}

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

/* Estilos para el feedback */
.feedback-text {
  font-family: 'game';
  font-weight: bold;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
  white-space: nowrap;
  position: absolute;
  transform: translateY(-50%);
  /* Forzar nueva capa para mejor rendimiento */
  will-change: transform, opacity;
  backface-visibility: hidden;
}

.feedback-left .feedback-text {
  left: 0;
  text-align: left;
}

.feedback-right .feedback-text {
  right: 0;
  text-align: right;
}

.perfect {
  @apply text-purple-400;
  font-size: 2.5rem;
}

.good {
  @apply text-pink-400;
  font-size: 2rem;
}

.miss {
  @apply text-red-500;
  font-size: 1.5rem;
}

/* Animaciones más rápidas */
.feedback-enter-active {
  animation: feedback-in 0.1s ease-out;
}

.feedback-leave-active {
  animation: feedback-out 0.1s ease-in;
}

@keyframes feedback-in {
  0% {
    opacity: 0;
    transform: translateY(-50%) translateX(-10px) scale(0.8);
  }
  100% {
    opacity: 1;
    transform: translateY(-50%) translateX(0) scale(1);
  }
}

@keyframes feedback-out {
  0% {
    opacity: 1;
    transform: translateY(-50%) translateX(0) scale(1);
  }
  100% {
    opacity: 0;
    transform: translateY(-50%) translateX(10px) scale(0.8);
  }
}

/* Efecto de brillo */
.perfect, .good {
  animation: glow 0.5s ease-in-out;
}

@keyframes glow {
  0%, 100% {
    filter: drop-shadow(0 0 5px currentColor);
  }
  50% {
    filter: drop-shadow(0 0 15px currentColor);
  }
}
</style> 
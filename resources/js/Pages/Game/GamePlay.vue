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

    <!-- Pantalla de inicio -->
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
    <div class="game-hud fixed top-0 left-0 right-0 p-4 flex justify-end items-center">
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

    <!-- Menú de pausa -->
    <div v-if="isPaused && gameStarted && !gameEnded" 
         class="fixed inset-0 bg-black/95 flex items-center justify-center z-[70]">
      <div class="text-center space-y-6 animate-fadeIn">
        <h2 class="text-4xl font-game mb-8">Pausa</h2>
        <div class="flex flex-col gap-4">
          <button @click="resumeGame" 
                  class="bg-gradient-to-r from-pink-600/40 to-purple-600/40 
                         hover:from-pink-500/60 hover:to-purple-500/60 
                         text-white px-8 py-3 rounded-lg transition-all
                         font-game text-xl border border-pink-500/30
                         hover:scale-105 hover:shadow-[0_0_15px_rgba(236,72,153,0.3)]">
            Continuar
          </button>
          <button @click="restartGame" 
                  class="bg-purple-900/30 hover:bg-purple-900/50 
                         text-white px-8 py-3 rounded-lg transition-all
                         font-game text-xl border border-purple-500/30">
            Reintentar
          </button>
          <button @click="exitGame" 
                  class="bg-pink-900/30 hover:bg-pink-900/50 
                         text-white px-8 py-3 rounded-lg transition-all
                         font-game text-xl border border-pink-500/30">
            Salir
          </button>
        </div>
      </div>
    </div>

    <!-- Área de juego -->
    <div class="game-area relative h-screen">
      <!-- Feedback de hits -->
      <div class="hit-feedback-container fixed inset-0 pointer-events-none z-40">
        <div class="feedback-left fixed left-[25%] bottom-[30%] w-[30%]">
          <transition name="feedback">
            <div v-if="leftFeedback" 
                 :key="leftFeedback.id"
                 class="feedback-text absolute"
                 :class="leftFeedback.type">
              {{ leftFeedback.text }}
            </div>
          </transition>
        </div>
        <div class="feedback-right fixed right-[25%] bottom-[30%] w-[30%]">
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
      <div class="timeline absolute bottom-20 left-0 right-0 h-1 bg-purple-500/30"></div>

      <!-- Carriles de notas -->
      <div class="note-lanes absolute bottom-20 left-[5%] flex gap-1 items-end">
        <div v-for="lane in 4" :key="lane" 
             class="note-lane w-20 relative" 
             style="height: calc(100vh + 5rem)">
          <div class="lane-background absolute inset-0"></div>
          <div class="note-receptor absolute bottom-0 w-full h-16 
                      bg-purple-900/40 rounded-lg border-2 border-purple-500/30
                      transition-all z-10"
               :class="{ 'active': activeKeys[lane-1] }">
            <span class="key-hint absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 
                         text-white/50 text-base font-bold">
              {{ ['D', 'F', 'J', 'K'][lane-1] }}
            </span>
          </div>
          <div v-for="note in notes.filter(n => n.lane === lane-1 && !n.hit && !n.missed)" 
               :key="note.timing"
               class="absolute w-full h-16 bg-purple-500/50 rounded-lg border-2 
                      border-purple-500/30 transition-transform z-20"
               :style="{ bottom: `${note.y}px` }">
          </div>
        </div>
      </div>
    </div>

    <!-- Pantalla de resultados -->
    <div v-if="gameEnded" class="fixed inset-0 flex items-center justify-center z-50">
      <!-- Fondo con blur y overlay más claro -->
      <div class="absolute inset-0 bg-[rgb(0_0_0_/27%)] backdrop-blur-md"></div>
      
      <!-- Contenedor principal con animación -->
      <div class="relative z-10 w-full max-w-4xl mx-auto results-container">
        <!-- Sección superior con información de la canción -->
        <div class="flex items-center gap-6 mb-8 px-8 results-stats-enter animate-delay-1">
          <div class="w-32 h-32 rounded-xl overflow-hidden border-2 border-white/10">
            <img :src="map.thumbnail_path" alt="Song thumbnail" class="w-full h-full object-cover">
          </div>
          <div class="flex-1 text-left">
            <h2 class="text-3xl font-game text-white mb-2">{{ map.title }}</h2>
            <p class="text-xl text-gray-400">{{ map.artist }}</p>
            <p class="text-sm text-gray-500 mt-1">Mapped by {{ map.creator }}</p>
          </div>
          <!-- Puntuación grande a la derecha -->
          <div class="text-right">
            <div class="text-7xl font-mono font-bold tracking-wider text-white">
              {{ score.toLocaleString() }}
            </div>
            <div class="text-lg text-purple-400">Puntuación Total</div>
          </div>
        </div>

        <!-- Contenedor principal de estadísticas -->
        <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 border border-white/10 results-stats-enter animate-delay-2">
          <!-- Estadísticas principales -->
          <div class="grid grid-cols-3 gap-8 mb-8">
            <!-- Precisión -->
            <div class="text-center">
              <div class="text-5xl font-bold mb-2 text-white">
                {{ accuracy.toFixed(2) }}%
              </div>
              <div class="text-purple-400">Precisión</div>
            </div>
            <!-- Combo Máximo -->
            <div class="text-center">
              <div class="text-5xl font-bold mb-2 text-white">
                {{ maxCombo }}x
              </div>
              <div class="text-pink-400">Combo Máximo</div>
            </div>
            <!-- Grado (basado en la precisión) -->
            <div class="text-center">
              <div class="text-7xl font-bold mb-2" :class="{
                'text-yellow-400': accuracy >= 95,  // S
                'text-purple-400': accuracy >= 90 && accuracy < 95,  // A
                'text-blue-400': accuracy >= 80 && accuracy < 90,  // B
                'text-green-400': accuracy >= 70 && accuracy < 80,  // C
                'text-red-400': accuracy < 70  // D
              }">
                {{ accuracy >= 95 ? 'S' : 
                   accuracy >= 90 ? 'A' : 
                   accuracy >= 80 ? 'B' : 
                   accuracy >= 70 ? 'C' : 'D' }}
              </div>
              <div class="text-gray-400">Grado</div>
            </div>
          </div>

          <!-- Detalles de hits con animación retrasada -->
          <div class="grid grid-cols-2 gap-x-16 gap-y-6 results-stats-enter animate-delay-3">
            <!-- Hits perfectos -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <span class="text-lg text-white">Perfectos</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="text-2xl font-bold text-white">
                  {{ notes.filter(n => n.hit && n.hitType === 'perfect').length }}
                </span>
                <span class="text-sm text-green-400">+100</span>
              </div>
            </div>

            <!-- Hits buenos -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <span class="text-lg text-white">Buenos</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="text-2xl font-bold text-white">
                  {{ notes.filter(n => n.hit && n.hitType === 'good').length }}
                </span>
                <span class="text-sm text-yellow-400">+50</span>
              </div>
            </div>

            <!-- Notas perdidas -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <span class="text-lg text-white">Perdidas</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="text-2xl font-bold text-white">
                  {{ notes.filter(n => n.missed).length }}
                </span>
                <span class="text-sm text-red-400">+0</span>
              </div>
            </div>

            <!-- Pulsaciones incorrectas -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-red-600"></div>
                <span class="text-lg text-white">Incorrectas</span>
              </div>
              <div class="flex items-center gap-4">
                <span class="text-2xl font-bold text-white">
                  {{ missedPresses }}
                </span>
                <span class="text-sm text-red-600">-10</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Botones de acción con animación final -->
        <div class="flex justify-center gap-6 mt-8 results-stats-enter animate-delay-4">
          <button @click="restartGame" 
                  class="bg-purple-500/20 hover:bg-purple-500/30 
                         text-white px-8 py-3 rounded-xl transition-all
                         font-game text-xl border border-purple-500/50
                         hover:scale-105 hover:shadow-[0_0_20px_rgba(168,85,247,0.3)]
                         flex items-center gap-2">
            <i class="fas fa-redo"></i>
            Reintentar
          </button>
          <button @click="exitGame" 
                  class="bg-pink-500/20 hover:bg-pink-500/30 
                         text-white px-8 py-3 rounded-xl transition-all
                         font-game text-xl border border-pink-500/50
                         hover:scale-105 hover:shadow-[0_0_20px_rgba(236,72,153,0.3)]
                         flex items-center gap-2">
            <i class="fas fa-home"></i>
            Volver al Menú
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

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
const PERFECT_WINDOW = 80 // ms
const GOOD_WINDOW = 200 // ms
const MISS_WINDOW = 300 // ms
const NOTE_OFFSET = 4000 // ms
const RESULTS_DELAY = 2000 // ms - Tiempo de espera antes de mostrar resultados (2 segundos por defecto)

// Textos aleatorios para cada tipo de feedback
const feedbackTexts = {
  perfect: ['PERFECT!', '¡INCREÍBLE!', '¡EXCELENTE!', '¡PERFECTO!', '¡ASOMBROSO!'],
  good: ['¡BIEN!', '¡GENIAL!', 'GOOD!', '¡SIGUE ASÍ!', '¡CONTINÚA!'],
  miss: ['¡MISS!', '¡OH NO!', '¡CASI!', '¡SIGUIENTE!', '¡CONCENTRATE!']
}

// Añadir estado de pausa
const isPaused = ref(false)

// Añadir nueva variable para contar fallos por pulsaciones incorrectas
const missedPresses = ref(0);

// Variable para controlar los logs
let lastLoggedTime = 0;

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
  
  // Log cada segundo (para no saturar la consola)
  if (Math.floor(audioElement.value.currentTime) !== Math.floor(lastLoggedTime)) {
    console.log('=== Debug updateNotes ===');
    console.log('Tiempo actual:', audioElement.value.currentTime);
    console.log('Duración total:', audioElement.value.duration);
    console.log('Notas restantes:', notes.value.filter(n => !n.hit && !n.missed).length);
    lastLoggedTime = audioElement.value.currentTime;
  }
  
  notes.value.forEach(note => {
    if (!note.hit && !note.missed) {
      const timeUntilHit = note.timing - currentTime.value;
      note.y = (timeUntilHit / 1000) * SCROLL_SPEED;
      
      // Marcar como perdida si pasó demasiado tiempo
      if (timeUntilHit < -MISS_WINDOW && note.y < -50) {
        note.missed = true;
        note.hitType = 'miss';
        combo.value = 0;
        // Actualizar precisión en tiempo real
        updateAccuracy();
        showHitFeedback('miss', note.lane);
      }
    }
  });
  
  checkGameEnd();
}

// Modificar la función updateAccuracy para incluir pulsaciones incorrectas
function updateAccuracy() {
  const totalNotes = notes.value.filter(note => note.hit || note.missed).length;
  const totalAttempts = totalNotes + missedPresses.value; // Incluir pulsaciones incorrectas
  if (totalAttempts === 0) return;

  const perfectHits = notes.value.filter(note => note.hit && note.hitType === 'perfect').length;
  const goodHits = notes.value.filter(note => note.hit && note.hitType === 'good').length;
  
  // Calcular precisión incluyendo pulsaciones incorrectas
  accuracy.value = ((perfectHits * 100 + goodHits * 50) / (totalAttempts * 100)) * 100;
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

// Modificar handleKeyDown para incluir más logs
const handleKeyDown = (event) => {
  if (event.key === 'Escape') {
    if (isPaused.value) {
      resumeGame();
    } else {
      pauseGame();
    }
    return;
  }
  
  if (isPaused.value || !gameStarted.value || showCountdown.value) return;
  
  const keyToLane = {
    'd': 0,
    'f': 1,
    'j': 2,
    'k': 3
  };

  const lane = keyToLane[event.key.toLowerCase()];
  if (lane === undefined) return;

  activeKeys.value[lane] = true;
  keyPressTime.value[lane] = Date.now();

  const currentTime = audioElement.value ? 
    (audioElement.value.currentTime * 1000) + NOTE_OFFSET :
    0;

  console.log('=== Debug Puntuación ===');
  console.log('Puntuación actual:', score.value);
  console.log('Combo actual:', combo.value);

  const nearestNote = notes.value.find(note => 
    note.lane === lane && 
    !note.hit &&
    !note.missed &&
    Math.abs(currentTime - note.timing) <= GOOD_WINDOW
  );

  if (nearestNote) {
    const timeDiff = Math.abs(currentTime - nearestNote.timing);
    
    if (timeDiff <= PERFECT_WINDOW) {
      nearestNote.hit = true;
      nearestNote.hitType = 'perfect';
      score.value += 100;
      combo.value++;
      maxCombo.value = Math.max(maxCombo.value, combo.value);
      console.log('Hit perfecto! Nueva puntuación:', score.value);
      updateAccuracy();
      showHitFeedback('perfect', nearestNote.lane);
    } else if (timeDiff <= GOOD_WINDOW) {
      nearestNote.hit = true;
      nearestNote.hitType = 'good';
      score.value += 50;
      combo.value++;
      maxCombo.value = Math.max(maxCombo.value, combo.value);
      console.log('Hit bueno! Nueva puntuación:', score.value);
      updateAccuracy();
      showHitFeedback('good', nearestNote.lane);
    }
  } else {
    // Penalización por presionar cuando no hay nota
    missedPresses.value++;
    combo.value = 0;
    // Reducir puntuación por fallo
    score.value = Math.max(0, score.value - 10); // Evita que baje de 0
    console.log('Miss! Nueva puntuación:', score.value);
    showHitFeedback('miss', lane);
    updateAccuracy();
  }
};

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
// Iniciar el juego
function startGame() {
  showCountdown.value = true;
  countdown.value = 3;
  if (videoBackground.value) {
    console.log('Preparando video')
    videoBackground.value.currentTime = 0
    videoBackground.value.pause()
  }
  const countdownInterval = setInterval(() => {
    countdown.value--;
    if (countdown.value === 0) {
      clearInterval(countdownInterval);
      setTimeout(() => {
        showCountdown.value = false;
        gameStarted.value = true;
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
        initializeNotes();
        
        // Retrasar el inicio del audio para dar tiempo a que aparezcan las notas
        setTimeout(() => {
          audioElement.value.play()
            .then(() => {
              console.log('Audio iniciado correctamente');
              gameLoop.value = setInterval(updateNotes, 16);
            })
            .catch(error => {
              console.error('Error al reproducir el audio:', error);
            });
        }, 500); // Pequeño retraso antes de iniciar el audio
      }, 1000);
    }
  }, 1000);
}

// Función para verificar si el juego ha terminado
function checkGameEnd() {
  if (!audioElement.value || !gameStarted.value || gameEnded.value) return;
  
  // Logs para depuración
  console.log('=== Debug checkGameEnd ===');
  console.log('Audio current time:', audioElement.value.currentTime);
  console.log('Audio duration:', audioElement.value.duration);
  console.log('Video current time:', videoBackground.value?.currentTime);
  console.log('Video duration:', videoBackground.value?.duration);
  console.log('Notas totales:', notes.value.length);
  console.log('Notas procesadas:', notes.value.filter(note => note.hit || note.missed).length);
  console.log('gameStarted:', gameStarted.value);
  console.log('gameEnded:', gameEnded.value);
  
  // Verificar si la canción ha terminado
  const audioTimeRemaining = audioElement.value.duration - audioElement.value.currentTime;
  console.log('Tiempo restante de audio:', audioTimeRemaining, 'segundos');
  
  // Verificar si todas las notas han sido procesadas
  const allNotesProcessed = notes.value.every(note => note.hit || note.missed);
  console.log('¿Todas las notas procesadas?:', allNotesProcessed);
  
  // Solo terminar si realmente el audio está cerca del final (menos de 0.5 segundos) o todas las notas han sido procesadas
  if ((audioTimeRemaining <= 0.5 && !audioElement.value.ended) || audioElement.value.ended) {
    console.log('Terminando juego por fin de audio');
    endGame();
  } else if (allNotesProcessed && audioTimeRemaining <= 0.5) {
    console.log('Terminando juego por notas completadas');
    endGame();
  }
}

// Terminar el juego
async function endGame() {
  if (gameEnded.value) return;
  
  // Detener el juego pero no mostrar resultados aún
  gameStarted.value = false;
  
  if (gameLoop.value) {
    clearInterval(gameLoop.value);
  }
  
  if (audioElement.value) {
    audioElement.value.pause();
  }
  
  if (videoBackground.value) {
    videoBackground.value.pause();
  }
  
  // Marcar todas las notas restantes como perdidas
  notes.value.forEach(note => {
    if (!note.hit && !note.missed) {
      note.missed = true;
      note.hitType = 'miss';
    }
  });
  
  // Actualizar precisión final
  updateAccuracy();

  // Log del estado final
  console.log('=== Debug Estado Final ===');
  console.log('Puntuación final:', score.value);
  console.log('Combo máximo:', maxCombo.value);
  console.log('Precisión:', accuracy.value);
  console.log('Notas perfectas:', notes.value.filter(n => n.hit && n.hitType === 'perfect').length);
  console.log('Notas buenas:', notes.value.filter(n => n.hit && n.hitType === 'good').length);
  console.log('Notas perdidas:', notes.value.filter(n => n.missed).length);
  console.log('Pulsaciones incorrectas:', missedPresses.value);

  // Esperar el tiempo configurado antes de mostrar los resultados
  await new Promise(resolve => setTimeout(resolve, RESULTS_DELAY));
  
  // Ahora sí mostrar la pantalla de resultados
  gameEnded.value = true;

  try {
    // Asegurarse de que la puntuación sea un número válido
    const finalScore = Math.max(0, Math.floor(score.value));
    console.log('Enviando puntuación al servidor:', finalScore);

    const response = await axios.post(route('game.rankings.save', { map: props.map.id }), {
      score: finalScore,
      max_combo: maxCombo.value,
      accuracy: accuracy.value
    });

    if (response.data.success) {
      console.log('Ranking guardado:', response.data.ranking);
      console.log('Mensaje:', response.data.message);
    }
  } catch (error) {
    console.error('Error al guardar ranking:', error);
  }
}

// Salir del juego
function exitGame() {
  isPaused.value = false
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
  window.location.reload()
}

// Nueva función para inicializar el juego
function initializeGame() {
  gameInitialized.value = true
  
  // Crear y cargar el audio primero
  audioElement.value = new Audio(props.map.audio_path)
  audioElement.value.load() // Precarga el audio
  
  // Luego iniciar el juego
  startGame()
}

// Función para pausar el juego
const pauseGame = () => {
  if (!gameStarted.value || gameEnded.value || showCountdown.value) return
  
  isPaused.value = true
  if (audioElement.value) {
    audioElement.value.pause()
  }
  if (videoBackground.value) {
    videoBackground.value.pause()
  }
  if (gameLoop.value) {
    clearInterval(gameLoop.value)
  }
}

// Función para reanudar el juego
const resumeGame = () => {
  isPaused.value = false
  if (audioElement.value) {
    audioElement.value.play()
  }
  if (videoBackground.value) {
    videoBackground.value.play()
  }
  gameLoop.value = setInterval(updateNotes, 16)
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

watch(() => props.map, async (newMap) => {
  await nextTick()
  handleBackgroundVideo()
})
</script>

<style scoped>
.game-container {
  @apply relative min-h-screen overflow-hidden;
}

.video-background {
  @apply fixed right-0 bottom-0 min-w-full min-h-full w-auto h-auto z-[-2]
         object-cover opacity-40 transition-opacity duration-700;
}

.video-overlay {
  @apply fixed inset-0 bg-gradient-to-r from-black/80 via-black/60 to-black/80 z-[-1];
}

.game-area {
  @apply flex h-screen;
}

.gameplay-lanes {
  @apply w-1/3 relative flex;
  margin-left: 5%;
}

.lanes-container {
  @apply flex w-full h-full relative;
}

.lane {
  @apply flex-1 relative mx-1;
}

.lane-background {
  @apply bg-gradient-to-b from-purple-900/40 via-purple-900/30 to-transparent
         border-l border-r border-purple-500/30;
  mask-image: linear-gradient(to top, black 90%, transparent);
}

.note-receptor {
  @apply shadow-lg backdrop-blur-sm
         bg-purple-900/40;
}

.note-receptor.active {
  @apply bg-purple-500/50 
         border-purple-500/70 
         scale-105 
         shadow-[0_0_15px_rgba(168,85,247,0.4)];
}

.key-hint {
  @apply opacity-70 transition-all duration-200;
}

.note-receptor.active .key-hint {
  @apply opacity-100 scale-110;
}

.note {
  @apply bg-gradient-to-b from-purple-500/50 to-pink-500/50
         backdrop-blur-sm;
}

/* Asegurar que las notas estén por encima del fondo del carril */
.note-receptor, 
.note {
  @apply rounded-lg shadow-md z-10;
}

.feedback-area {
  @apply absolute right-[-150%] w-64 h-full
         pointer-events-none;
}

.feedback-container {
  @apply absolute h-full flex flex-col items-start justify-center;
}

.feedback-container.left {
  @apply left-0;
}

.feedback-container.right {
  @apply right-0;
}

.feedback-text {
  @apply font-bold text-shadow-glow whitespace-nowrap
         transform transition-all duration-100;
}

.perfect {
  @apply text-4xl text-purple-400;
}

.good {
  @apply text-3xl text-pink-400;
}

.miss {
  @apply text-2xl text-red-500;
}

.game-info {
  @apply fixed top-8 right-8 text-right;
}

.score {
  @apply text-6xl font-bold text-white/90
         text-shadow-glow mb-2;
}

.combo {
  @apply text-3xl font-bold text-purple-400
         text-shadow-glow;
}

/* Utilidad para el brillo del texto */
.text-shadow-glow {
  text-shadow: 0 0 10px currentColor;
}

/* Animaciones de feedback */
.feedback-enter-active,
.feedback-leave-active {
  @apply transition-all duration-100;
}

.feedback-enter-from,
.feedback-leave-to {
  @apply opacity-0 scale-75;
}

/* Animación de brillo para hits perfectos */
@keyframes perfect-glow {
  0%, 100% { filter: drop-shadow(0 0 5px currentColor); }
  50% { filter: drop-shadow(0 0 15px currentColor); }
}

.perfect {
  animation: perfect-glow 0.5s ease-in-out;
}

.note-lanes {
  transform: translateX(0);
}

.note-lane {
  position: relative;
  transform-origin: bottom;
}

.note-lane::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(168, 85, 247, 0.1), transparent);
  border-left: 1px solid rgba(168, 85, 247, 0.2);
  border-right: 1px solid rgba(168, 85, 247, 0.2);
  pointer-events: none;
}

.key-hint {
  @apply opacity-70 transition-all duration-200;
}

.note-receptor.active .key-hint {
  @apply opacity-100 scale-110;
}

/* Ajustar el tamaño de las notas para que coincidan con los carriles más anchos */
.note-receptor, 
.note {
  @apply rounded-lg shadow-md;
}

.note {
  @apply bg-gradient-to-b from-purple-500/50 to-pink-500/50;
}

/* Animación para el menú de pausa */
.animate-fadeIn {
  animation: fadeIn 0.2s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animaciones para la pantalla de resultados */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-100px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.results-enter-active {
  animation: slideDown 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.results-container {
  animation: slideDown 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.results-stats-enter {
  animation: fadeIn 0.5s ease forwards;
  animation-delay: 0.3s;
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Clases para las animaciones secuenciales */
.animate-delay-1 {
  animation-delay: 0.2s;
}
.animate-delay-2 {
  animation-delay: 0.4s;
}
.animate-delay-3 {
  animation-delay: 0.6s;
}
.animate-delay-4 {
  animation-delay: 0.8s;
}
</style> 
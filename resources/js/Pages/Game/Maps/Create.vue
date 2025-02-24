<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Header con controles de audio -->
    <div class="bg-black border-b border-purple-900">
      <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
          <!-- Título y navegación -->
          <div class="flex items-center gap-4">
            <Link :href="route('maps.index')" 
                  class="text-white/70 hover:text-white transition-colors">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <h1 class="text-2xl font-game bg-gradient-to-r from-white via-purple-400 to-pink-500 text-transparent bg-clip-text">
              MAP/CREATOR
            </h1>
          </div>

          <!-- Controles de Audio -->
          <div class="flex items-center gap-4 flex-1 max-w-2xl mx-4">
            <span class="text-white/70 font-game text-sm">{{ formatTime(currentTime) }}</span>
            
            <div class="flex-1">
              <div class="audio-progress-container" 
                   @click="handleProgressClick($event, 'left')">
                <div class="audio-progress-bar"
                     :class="{ 'progress-complete': progressPercentage > 50 }"
                     :style="{ 
                       width: progressPercentage <= 50 
                         ? (progressPercentage * 2) + '%' 
                         : '100%'
                     }">
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <button @click="togglePlayback" 
                      class="audio-control-btn">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="!isPlaying" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path v-else
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 9v6m4-6v6" />
                </svg>
              </button>
              <span class="text-white/70 font-game text-sm">{{ formatTime(duration) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <GameHeader 
      title="MAP" 
      subtitle="CREATOR"
      :show-back="true"
      :back-url="route('maps.index')"
    >
      <template #actions>
        <button v-if="audioLoaded" 
                @click="togglePlayback" 
                class="editor-button">
          {{ isPlaying ? '⏸️ Pausar' : '▶️ Reproducir' }}
        </button>
        <span v-if="audioLoaded" class="text-white font-mono">
          {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
        </span>
      </template>
    </GameHeader>

    <!-- Editor principal -->
    <div class="flex-1 grid grid-cols-12 h-[calc(100vh-5rem)]">
      <!-- Timeline y carriles -->
      <div class="col-span-9 bg-black/80 relative"
           @mousemove="updateMousePosition">
        <!-- Receptores fijos en la parte inferior -->
        <div class="fixed-receptors">
          <!-- Controles de Audio -->
          <div class="audio-controls-container mb-8">
            <div class="flex items-center gap-4 px-4 py-3">
              <span class="text-white/70 font-game text-sm">{{ formatTime(currentTime) }}</span>
              
              <div class="flex-1 relative flex items-center gap-4">
                <!-- Primera mitad de la barra -->
                <div class="flex-1">
                  <div class="audio-progress-container" 
                       @click="handleProgressClick($event, 'left')">
                    <div class="audio-progress-bar"
                         :class="{ 'progress-complete': progressPercentage > 50 }"
                         :style="{ 
                           width: progressPercentage <= 50 
                             ? (progressPercentage * 2) + '%' 
                             : '100%'
                         }">
                    </div>
                  </div>
                </div>

                <!-- Botón de Play/Pause centrado -->
                <div class="play-pause-container">
                  <button @click="togglePlayback" 
                          class="audio-control-btn">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path v-if="!isPlaying" 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 9v6m4-6v6" />
                    </svg>
                  </button>
                </div>

                <!-- Segunda mitad de la barra -->
                <div class="flex-1">
                  <div class="audio-progress-container" 
                       @click="handleProgressClick($event, 'right')">
                    <div class="audio-progress-bar"
                         :style="{ 
                           width: progressPercentage > 50 
                             ? ((progressPercentage - 50) * 2) + '%' 
                             : '0%'
                         }">
                    </div>
                  </div>
                </div>
              </div>

              <span class="text-white/70 font-game text-sm">{{ formatTime(duration) }}</span>
            </div>
          </div>

          <!-- Receptores de teclas -->
          <div class="flex">
            <div v-for="i in 4" :key="i" class="flex-1">
              <div class="lane-receptor">
                <div class="key-hint">
                  {{ ['D', 'F', 'J', 'K'][i-1] }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Editor con scroll -->
        <div class="editor-container">
          <div v-if="audioLoaded" 
               class="relative"
               :style="{ height: `${duration * pixelsPerBeat}px` }"
               @mousedown="handleSelection"
               @contextmenu.prevent="handleRightClick">
            <!-- Carriles para notas -->
            <div class="absolute inset-0 flex">
              <div v-for="i in 4" :key="i" 
                   class="lane-column relative"
                   @click="addNoteAtPosition($event, i-1)">
                <!-- Grid de beats -->
                <div class="beat-grid">
                  <template v-if="formData.bpm">
                    <!-- Líneas principales (1/1) -->
                    <div v-for="beat in totalBeats" :key="`main-${beat}`"
                         class="beat-line main"
                         :style="{ top: `${beat * beatSpacing}px` }">
                    </div>

                    <!-- Líneas de medio beat (1/2) -->
                    <template v-if="snapDivision >= 2">
                      <div v-for="beat in totalBeats * 2" :key="`half-${beat}`"
                           class="beat-line half"
                           :style="{ top: `${beat * (beatSpacing/2)}px` }">
                      </div>
                    </template>

                    <!-- Líneas de cuarto (1/4) -->
                    <template v-if="snapDivision >= 4">
                      <div v-for="beat in totalBeats * 4" :key="`quarter-${beat}`"
                           class="beat-line quarter"
                           :style="{ top: `${beat * (beatSpacing/4)}px` }">
                      </div>
                    </template>

                    <!-- Líneas de octavo (1/8) -->
                    <template v-if="snapDivision >= 8">
                      <div v-for="beat in totalBeats * 8" :key="`eighth-${beat}`"
                           class="beat-line eighth"
                           :style="{ top: `${beat * (beatSpacing/8)}px` }">
                      </div>
                    </template>
                  </template>
                </div>
                
                <!-- Notas -->
                <div v-for="note in notesInLane(i-1)" 
                     :key="note.id"
                     :data-note-id="note.id"
                     class="note-wrapper"
                     :class="{ 
                       'selected': selection.selectedNotes.includes(note),
                       'dragging': dragState.isDragging && dragState.selectedNotes.includes(note)
                     }"
                     :style="{ 
                       top: `${note.time * pixelsPerBeat}px`,
                       cursor: selection.selectedNotes.includes(note) ? 'move' : 'pointer'
                     }"
                     @click="(e) => handleNoteClick(e, note)"
                     @mousedown="(e) => startNoteDrag(e, note)">
                  <div class="note-block"></div>
                </div>
              </div>
            </div>

            <!-- Playhead -->
            <div class="playhead" 
                 :style="{ top: `${currentTime * pixelsPerBeat}px` }">
            </div>

            <!-- Área de selección -->
            <div v-if="selection.active && selection.startTime !== selection.endTime"
                 class="selection-area"
                 :style="{
                   top: `${Math.min(selection.startTime, selection.endTime) * pixelsPerBeat}px`,
                   height: `${Math.abs(selection.endTime - selection.startTime) * pixelsPerBeat}px`
                 }">
            </div>
          </div>
          
          <!-- Mensaje de subir audio -->
          <div v-else class="h-full flex items-center justify-center">
            <div class="text-center">
              <p class="text-gray-400 font-game mb-4">Sube un archivo de audio para comenzar</p>
              <input 
                type="file"
                @change="handleAudioUpload"
                accept="audio/*"
                class="config-input file:bg-purple-600/40 file:border-0 
                       file:text-white file:font-game file:rounded-lg
                       file:px-4 file:py-2 file:mr-4
                       hover:file:bg-purple-500/60"
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Panel de configuración -->
      <div class="col-span-3 bg-black border-l border-purple-900 p-4 overflow-y-auto">
        <div class="space-y-4">
          <!-- Info básica -->
          <div class="map-config-item">
            <label class="text-white font-game mb-1 block">Título</label>
            <input v-model="formData.title" type="text" class="config-input">
          </div>

          <div class="map-config-item">
            <label class="text-white font-game mb-1 block">Artista</label>
            <input v-model="formData.artist" type="text" class="config-input">
          </div>

          <!-- Teclas para mapeo -->
          <div class="map-config-item">
            <label class="text-white font-game mb-1 block">Teclas para mapeo</label>
            <div class="grid grid-cols-4 gap-1">
              <div v-for="key in ['D','F','J','K']" :key="key"
                   class="bg-purple-900/30 py-1 px-2 rounded-lg border border-purple-500/30 text-center">
                <span class="text-white text-sm">{{ key }}</span>
              </div>
            </div>
          </div>

          <div class="map-config-item">
            <label class="text-white font-game mb-1 block">BPM</label>
            <input 
              v-model="formData.bpm"
              type="number"
              min="1"
              class="config-input"
              placeholder="Ej: 120"
            >
          </div>

          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Dificultad</label>
            <select v-model="formData.difficulty" class="config-input">
              <option value="easy">Fácil</option>
              <option value="normal">Normal</option>
              <option value="hard">Difícil</option>
              <option value="expert">Experto</option>
            </select>
          </div>

          <!-- Control de velocidad de scroll -->
          <div class="space-y-2">
            <label class="text-white">Velocidad de Scroll</label>
            <div class="custom-range-container"
                 @mousedown="startDragging"
                 @mousemove="handleDrag"
                 @mouseup="stopDragging"
                 @mouseleave="stopDragging"
                 ref="rangeContainer">
              <!-- Track base -->
              <div class="range-track">
                <!-- Track de progreso -->
                <div class="range-progress" 
                     :style="{ width: `${((scrollSpeed - 50) / 150) * 100}%` }">
                </div>
              </div>
              <!-- Thumb -->
              <div class="range-thumb" 
                   :style="{ left: `${((scrollSpeed - 50) / 150) * 100}%` }"
                   :class="{ 'dragging': isDragging }">
                <!-- Valor actual -->
                <div class="range-value">{{ scrollSpeed }}px</div>
              </div>
              <!-- Input hidden para mantener el valor -->
              <input type="hidden" v-model="scrollSpeed">
            </div>
          </div>

          <!-- Control de Snap -->
          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Snap a Beat</label>
            <div class="grid grid-cols-4 gap-2">
              <button v-for="snap in [1, 2, 4, 8]" 
                      :key="snap"
                      @click="snapDivision = snap"
                      :class="[
                        'snap-button',
                        snapDivision === snap ? 'active' : ''
                      ]">
                1/{{ snap }}
              </button>
            </div>
            <!-- Visualización del snap actual -->
            <div class="mt-4 bg-purple-900/30 rounded-lg p-2">
              <div class="snap-preview">
                <div v-for="n in 4" :key="n" class="snap-marker"
                     :class="{ 'visible': n <= snapDivision }">
                </div>
              </div>
            </div>
          </div>

          <!-- Ayudas de mapeo -->
          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Ayudas</label>
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-gray-400">
                <input type="checkbox" v-model="showMetronome">
                Metrónomo
              </label>
              <label class="flex items-center gap-2 text-gray-400">
                <input type="checkbox" v-model="showBeatLines">
                Líneas de beat
              </label>
            </div>
          </div>

          <!-- Botón de guardar -->
          <button @click="saveMap"
                  class="w-full bg-purple-600 hover:bg-purple-500 
                         text-white font-game py-3 px-4 rounded-lg
                         transition-colors duration-200
                         flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
            </svg>
            Guardar Mapa
          </button>
        </div>
      </div>
    </div>

    <!-- Mensajes de error con mejor posicionamiento -->
    <div class="error-container">
      <TransitionGroup name="error">
        <div v-for="error in errorMessages"
             :key="error.id"
             class="error-message"
             :style="{
               left: `${error.x}px`,
               top: `${error.y}px`
             }">
          {{ error.message }}
        </div>
      </TransitionGroup>
    </div>

    <!-- Sistema de notificaciones -->
    <div class="fixed bottom-4 right-4 space-y-2 z-50">
      <TransitionGroup name="notification">
        <div v-for="notification in notifications"
             :key="notification.id"
             class="notification-container"
             :class="notification.type">
          <div class="flex items-center gap-2">
            <!-- Icono de éxito -->
            <svg v-if="notification.type === 'success'" 
                 class="w-5 h-5 text-emerald-400" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Icono de error -->
            <svg v-else 
                 class="w-5 h-5 text-red-400" 
                 fill="none" 
                 viewBox="0 0 24 24" 
                 stroke="currentColor">
              <path stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ notification.message }}
          </div>
          <!-- Barra de progreso -->
          <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-pink-500 to-purple-600 rounded-b-lg"
               :style="{ width: `${notification.progress}%` }">
          </div>
        </div>
      </TransitionGroup>
    </div>

    <!-- Spinner -->
    <Spinner v-if="isSaving" message="Guardando mapa..." />

    <!-- Añadir componente de notificaciones -->
    <NotificationSystem ref="notificationSystem" />

    <!-- Reemplazar el div de atajos existente por este -->
    <div class="fixed bottom-4 right-4 flex flex-col items-end space-y-2">
      <!-- Panel de atajos -->
      <Transition name="slide-fade">
        <div v-if="showShortcuts" 
             class="bg-black/80 p-4 rounded-lg border border-purple-500/30
                    transform transition-all duration-300">
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-white font-game">Atajos de teclado</h3>
            <button @click="showShortcuts = false"
                    class="text-white/50 hover:text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <ul class="space-y-1">
            <li v-for="shortcut in shortcuts" 
                :key="shortcut.key" 
                class="text-sm">
              <span class="text-purple-400">{{ shortcut.key }}</span>
              <span class="text-white/70">: {{ shortcut.description }}</span>
            </li>
          </ul>
        </div>
      </Transition>

      <!-- Botón para mostrar atajos -->
      <button v-if="!showShortcuts"
              @click="showShortcuts = true"
              class="bg-purple-900/80 p-2 rounded-lg border border-purple-500/30
                     text-white/70 hover:text-white
                     transform transition-all duration-300
                     hover:bg-purple-800/90 hover:scale-105">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted, onMounted } from 'vue'
import axios from 'axios'
import Spinner from '@/Components/Spinner.vue'
import { router } from '@inertiajs/vue3'
import GameHeader from '@/Components/GameHeader.vue'
import NotificationSystem from '@/Components/NotificationSystem.vue'

const audioLoaded = ref(false)
const isPlaying = ref(false)
const currentTime = ref(0)
const duration = ref(0)
const scrollSpeed = ref(100)
const snapDivision = ref(4)
const showMetronome = ref(true)
const showBeatLines = ref(true)
const notes = ref([])

const audio = ref(null)
const audioContext = ref(null)
const audioSource = ref(null)
const startTime = ref(0)

const pixelsPerBeat = computed(() => scrollSpeed.value)

const formData = ref({
  title: '',
  artist: '',
  bpm: 120,
  difficulty: 'normal',
  audio: null,
  notes: []
})

const errorMessages = ref([])

const isDragging = ref(false)
const rangeContainer = ref(null)

// Valores mínimo y máximo
const minValue = 50
const maxValue = 200

// Calcular el porcentaje de progreso
const progressPercentage = computed(() => {
  if (!duration.value) return 0
  return (currentTime.value / duration.value) * 100
})

const notifications = ref([])
const isSaving = ref(false)

// Estado para la selección
const selection = ref({
  active: false,
  startTime: null,
  endTime: null,
  selectedNotes: []
})

// Teclas para copiar/pegar
const COPY_KEY = 'c'
const PASTE_KEY = 'v'
const SELECT_KEY = 'a'
const clipboard = ref([])

// Añadir referencia al sistema de notificaciones
const notificationSystem = ref(null)

// Estado para el arrastre de notas
const dragState = ref({
  isDragging: false,
  wasDragging: false,
  startY: 0,
  selectedNotes: [],
  originalPositions: []
})

// Añadir variable para el timeout
let clickTimeout = null
const CLICK_DELAY = 150 // milisegundos de delay

// Añadir ref para la posición del mouse
const mousePosition = ref({ x: 0, y: 0 })

// Añadir ref para controlar visibilidad
const showShortcuts = ref(true) // Inicialmente visible

function handleAudioUpload(event) {
  const file = event.target.files[0]
  if (file) {
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg']
    if (!validTypes.includes(file.type)) {
      showNotification('Formato de audio no válido. Use MP3, WAV o OGG', 'error')
      event.target.value = ''
      return
    }

    const maxSize = 10 * 1024 * 1024
    if (file.size > maxSize) {
      showNotification('El archivo es demasiado grande. Máximo 10MB', 'error')
      event.target.value = ''
      return
    }

    formData.value.audio = file
    audio.value = new Audio(URL.createObjectURL(file))
    audio.value.addEventListener('loadedmetadata', () => {
      duration.value = audio.value.duration
      audioLoaded.value = true
    })
  }
}

function togglePlayback() {
  if (audioLoaded.value) {
    if (isPlaying.value) {
      // Pausar
      audio.value.pause()
      isPlaying.value = false
    } else {
      // Reproducir
      audio.value.currentTime = currentTime.value
      audio.value.play()
      isPlaying.value = true
      updatePlaybackTime()
    }
  }
}

function updatePlaybackTime() {
  if (isPlaying.value) {
    currentTime.value = audio.value.currentTime
    requestAnimationFrame(updatePlaybackTime)
  }
}

function addNoteAtPosition(event, lane) {
  const editorContainer = document.querySelector('.editor-container')
  const rect = editorContainer.getBoundingClientRect()
  
  // Calcular posición exacta del click
  const clickY = event.clientY - rect.top + editorContainer.scrollTop
  
  const beatDuration = 60 / formData.value.bpm
  const snapDuration = beatDuration / snapDivision.value
  
  // Calcular tiempo basado en la posición exacta del click
  let time = clickY / pixelsPerBeat.value
  
  // Aplicar snap si está activado
  if (formData.value.bpm) {
    time = Math.round(time / snapDuration) * snapDuration
  }

  // Crear la nota en la posición exacta
  notes.value.push({
    id: Date.now(),
    lane,
    time
  })
  
  // Mantener las notas ordenadas por tiempo
  notes.value.sort((a, b) => a.time - b.time)
}

// Función para eliminar nota específica
function removeNote(note) {
  const index = notes.value.findIndex(n => n.id === note.id)
  if (index !== -1) {
    notes.value.splice(index, 1)
  }
}

// Función para resaltar nota (solo cuando hay conflicto real)
function highlightNote(noteId) {
  const noteElement = document.querySelector(`.note-wrapper[data-note-id="${noteId}"]`)
  if (noteElement) {
    noteElement.classList.add('highlight-error')
    setTimeout(() => {
      if (noteElement && noteElement.classList) {
        noteElement.classList.remove('highlight-error')
      }
    }, 1000)
  }
}

// Función para validar espaciado de notas
function validateNoteSpacing() {
  const beatDuration = 60 / formData.value.bpm
  const minSpacing = beatDuration * 0.5
  
  // Validar por carril
  for (let lane = 0; lane < 4; lane++) {
    const laneNotes = notes.value
      .filter(note => note.lane === lane)
      .sort((a, b) => a.time - b.time)
    
    for (let i = 0; i < laneNotes.length - 1; i++) {
      const spacing = laneNotes[i + 1].time - laneNotes[i].time
      if (spacing < minSpacing) {
        highlightNote(laneNotes[i].id)
        highlightNote(laneNotes[i + 1].id)
      }
    }
  }
}

function showError(message, event) {
  // Calcular posición exacta para el mensaje
  const error = {
    id: Date.now(),
    message,
    x: event.clientX,
    y: event.clientY - 20 // Desplazar un poco arriba para mejor visibilidad
  }
  
  errorMessages.value.push(error)
  setTimeout(() => {
    errorMessages.value = errorMessages.value.filter(e => e.id !== error.id)
  }, 2000)
}

// Ajustar el tamaño visual de las notas
const noteHeight = computed(() => {
  if (!formData.value.bpm) return 48
  
  const beatDuration = 60 / formData.value.bpm
  const snapDuration = beatDuration / snapDivision.value
  
  // Ajustar tamaño según el snap
  return Math.max(
    snapDuration * pixelsPerBeat.value * 0.8,
    32 // tamaño mínimo
  )
})

// Validar espaciado al intentar colocar nota
function isValidSpacing(time, lane) {
  const beatDuration = 60 / formData.value.bpm
  const snapDuration = beatDuration / snapDivision.value
  
  return !notes.value.some(note => 
    note.lane === lane && 
    Math.abs(note.time - time) <= snapDuration
  )
}

// Función para verificar si una posición es válida
function isValidNotePosition(time, lane) {
  const minSpacing = formData.value.bpm ? (60 / formData.value.bpm / snapDivision.value) * 0.9 : 0.1
  return !notes.value.some(note => 
    note.lane === lane && 
    Math.abs(note.time - time) < minSpacing
  )
}

// Función auxiliar para debug
function logClickPosition(event, lane) {
  const editorContainer = document.querySelector('.editor-container')
  const clickY = event.clientY
  const containerRect = editorContainer.getBoundingClientRect()
  const relativeY = clickY - containerRect.top + editorContainer.scrollTop
  console.log('Click position:', {
    clickY,
    containerTop: containerRect.top,
    scroll: editorContainer.scrollTop,
    relativeY,
    time: relativeY / pixelsPerBeat.value
  })
}

function snapToGrid(time) {
  if (!formData.value.bpm) return time
  
  const beatDuration = 60 / formData.value.bpm
  const snapDuration = beatDuration / snapDivision.value
  return Math.round(time / snapDuration) * snapDuration
}

function formatTime(seconds) {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = Math.round(seconds % 60)
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

function notesInLane(lane) {
  return notes.value
    .filter(note => note.lane === lane)
    .sort((a, b) => a.time - b.time) // Ordenar por tiempo
}

// Función para calcular los beats visibles
const visibleBeats = computed(() => {
  if (!formData.value.bpm || !duration.value) return []
  
  const beatDuration = 60 / formData.value.bpm
  const totalBeats = Math.ceil(duration.value / beatDuration)
  return Array.from({ length: totalBeats }, (_, i) => i)
})

// Función para calcular la posición vertical basada en el tiempo actual
const scrollPosition = computed(() => {
  return currentTime.value * pixelsPerBeat.value
})

// Actualizar el scroll del contenedor cuando cambia el tiempo
watch(scrollPosition, (newPos) => {
  if (isPlaying.value) {
    const container = document.querySelector('.editor-container')
    if (container) {
      container.scrollTop = newPos - (container.clientHeight / 2)
    }
  }
})

// Añadir watch para actualizar posiciones cuando cambie el BPM o snapDivision
watch([() => formData.value.bpm, () => snapDivision.value], () => {
  if (notes.value.length > 0) {
    notes.value = notes.value.map(note => ({
      ...note,
      time: snapToGrid(note.time)
    }))
  }
})

// Función para calcular y mostrar las líneas de snap
const snapLines = computed(() => {
  if (!formData.value.bpm || !duration.value) return []
  
  const beatDuration = 60 / formData.value.bpm
  const snapDuration = beatDuration / snapDivision.value
  const totalSnaps = Math.ceil(duration.value / snapDuration)
  
  return Array.from({ length: totalSnaps }, (_, i) => ({
    position: i * snapDuration * pixelsPerBeat.value,
    isBeat: i % snapDivision.value === 0,
    isHalfBeat: i % (snapDivision.value / 2) === 0
  }))
})

// Cálculos para las líneas de beat
const beatSpacing = computed(() => pixelsPerBeat.value * (60 / formData.value.bpm))
const totalBeats = computed(() => Math.ceil(duration.value / (60 / formData.value.bpm)))

// Iniciar el arrastre
function startDragging() {
  isDragging.value = true
}

// Manejar el arrastre
function handleDrag(event) {
  if (!isDragging.value || !rangeContainer.value) return
  
  const rect = rangeContainer.value.getBoundingClientRect()
  const percentage = (event.clientX - rect.left) / rect.width
  
  // Limitar entre 50 y 200
  scrollSpeed.value = Math.min(200, Math.max(50, Math.round(percentage * 150 + 50)))
}

// Detener el arrastre
function stopDragging() {
  isDragging.value = false
}

async function saveMap() {
  try {
    if (!formData.value.title.trim()) {
      showNotification('El título es requerido', 'error')
      return
    }
    if (!formData.value.artist.trim()) {
      showNotification('El artista es requerido', 'error')
      return
    }
    if (!formData.value.audio) {
      showNotification('Debes subir un archivo de audio', 'error')
      return
    }

    // Activar spinner
    isSaving.value = true

    const submitData = new FormData()
    submitData.append('title', formData.value.title.trim())
    submitData.append('artist', formData.value.artist.trim())
    submitData.append('bpm', formData.value.bpm)
    submitData.append('difficulty', formData.value.difficulty)
    submitData.append('audio', formData.value.audio)
    submitData.append('notes', JSON.stringify(notes.value))

    const response = await axios.post(route('maps.store'), submitData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json'
      }
    })

    if (response.data.success) {
      showNotification('¡Mapa guardado con éxito!')
      setTimeout(() => {
        router.visit(route('maps.index'))
      }, 1500)
    }
  } catch (error) {
    console.error('Error completo:', error)
    
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      if (errors) {
        const firstError = Object.values(errors)[0][0]
        showNotification(firstError, 'error')
      } else {
        showNotification('Error de validación en el formulario', 'error')
      }
    } else {
      showNotification(error.response?.data?.message || 'Error al guardar el mapa', 'error')
    }
  } finally {
    // Desactivar spinner
    isSaving.value = false
  }
}

// Función para manejar la selección
const handleSelection = (event) => {
  if (!event.ctrlKey) return
  
  event.preventDefault() // Prevenir comportamiento por defecto
  
  const editorContainer = document.querySelector('.editor-container')
  const rect = editorContainer.getBoundingClientRect()
  const clickY = event.clientY - rect.top + editorContainer.scrollTop
  const timeAtClick = clickY / pixelsPerBeat.value

  if (event.shiftKey && selection.value.startTime !== null) {
    // Extender selección
    selection.value.endTime = timeAtClick
    updateSelectedNotes()
  } else {
    // Nueva selección
    selection.value.active = true
    selection.value.startTime = timeAtClick
    selection.value.endTime = timeAtClick
    updateSelectedNotes()
  }
}

// Actualizar notas seleccionadas
const updateSelectedNotes = () => {
  if (!selection.value.startTime || !selection.value.endTime) return

  const start = Math.min(selection.value.startTime, selection.value.endTime)
  const end = Math.max(selection.value.startTime, selection.value.endTime)

  // Mantener las notas ya seleccionadas si se presiona Ctrl
  const currentSelection = selection.value.selectedNotes
  const newSelection = notes.value.filter(note => 
    note.time >= start && note.time <= end
  )

  // Combinar selecciones si se presiona Ctrl
  selection.value.selectedNotes = [...new Set([...currentSelection, ...newSelection])]
}

// Modificar handleNoteClick
const handleNoteClick = (event, note) => {
  event.stopPropagation()
  
  // Si acabamos de terminar de arrastrar, no procesar el click
  if (dragState.value.isDragging || isDragging.value) {
    isDragging.value = false
    dragState.value.isDragging = false
    return
  }

  if (clickTimeout) {
    clearTimeout(clickTimeout)
  }

  clickTimeout = setTimeout(() => {
    if (event.ctrlKey) {
      // Verificar si ya hay notas seleccionadas
      if (selection.value.selectedNotes.length > 0) {
        // Solo permitir seleccionar notas del mismo carril
        const firstNote = selection.value.selectedNotes[0]
        if (note.lane !== firstNote.lane) {
          showNotification('Solo puedes seleccionar notas del mismo carril', 'error')
          return
        }
      }

      const index = selection.value.selectedNotes.findIndex(n => n.id === note.id)
      if (index === -1) {
        selection.value.selectedNotes.push(note)
      } else {
        selection.value.selectedNotes.splice(index, 1)
      }
    } else {
      // Solo eliminar si no estamos justo después de arrastrar
      if (!dragState.value.wasDragging) {
        removeNote(note)
        selection.value.selectedNotes = selection.value.selectedNotes.filter(n => n.id !== note.id)
      }
    }
  }, CLICK_DELAY)
}

// Funciones para el arrastre de notas
const startNoteDrag = (event, note) => {
  event.stopPropagation()
  
  const selectedNotes = selection.value.selectedNotes.includes(note) 
    ? selection.value.selectedNotes 
    : [note]

  dragState.value = {
    isDragging: true,
    wasDragging: false,
    startY: event.clientY,
    selectedNotes,
    originalPositions: selectedNotes.map(n => ({ id: n.id, time: n.time }))
  }

  document.addEventListener('mousemove', handleNoteDrag)
  document.addEventListener('mouseup', stopNoteDrag)
}

const handleNoteDrag = (event) => {
  if (!dragState.value.isDragging) return

  const deltaY = event.clientY - dragState.value.startY
  const timeDelta = deltaY / pixelsPerBeat.value

  dragState.value.selectedNotes.forEach((note, index) => {
    const originalTime = dragState.value.originalPositions[index].time
    const newTime = snapToGrid(originalTime + timeDelta)
    if (newTime >= 0 && newTime <= duration.value) {
      note.time = newTime
    }
  })
}

const stopNoteDrag = () => {
  dragState.value.isDragging = false
  dragState.value.wasDragging = true // Marcar que acabamos de arrastrar
  
  // Resetear el estado de arrastre después de un momento
  setTimeout(() => {
    dragState.value.wasDragging = false
  }, CLICK_DELAY * 2)

  document.removeEventListener('mousemove', handleNoteDrag)
  document.removeEventListener('mouseup', stopNoteDrag)
}

// Añadir función para actualizar la posición del mouse
const updateMousePosition = (event) => {
  const lanesContainer = document.querySelector('.col-span-9')
  if (lanesContainer) {
    const rect = lanesContainer.getBoundingClientRect()
    mousePosition.value = {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top
    }
  }
}

// Modificar el handleKeyboard para deseleccionar después de pegar
const handleKeyboard = (event) => {
  if (!event.ctrlKey) return

  if (event.key.toLowerCase() === COPY_KEY) {
    if (selection.value.selectedNotes.length > 0) {
      const firstNoteTime = Math.min(...selection.value.selectedNotes.map(n => n.time))
      clipboard.value = selection.value.selectedNotes.map(note => ({
        ...note,
        timeOffset: note.time - firstNoteTime,
        originalLane: note.lane
      }))
      showNotification(`Copiadas ${selection.value.selectedNotes.length} notas del carril ${clipboard.value[0].originalLane + 1}`)
    } else {
      showNotification('No hay notas seleccionadas para copiar', 'error')
    }
  }

  if (event.key.toLowerCase() === PASTE_KEY) {
    if (clipboard.value.length > 0) {
      const lanesContainer = document.querySelector('.col-span-9')
      if (!lanesContainer) return

      const rect = lanesContainer.getBoundingClientRect()
      const laneWidth = rect.width / 4
      const targetLane = Math.floor(mousePosition.value.x / laneWidth)

      if (targetLane >= 0 && targetLane < 4) {
        const pasteTime = currentTime.value
        const newNotes = clipboard.value.map(note => ({
          id: Date.now() + Math.random(),
          lane: targetLane,
          time: pasteTime + note.timeOffset
        }))
        
        notes.value.push(...newNotes)
        notes.value.sort((a, b) => a.time - b.time)
        
        // Deseleccionar las notas después de pegar
        selection.value.selectedNotes = []
        
        showNotification(`Pegadas ${newNotes.length} notas en carril ${targetLane + 1}`)
      } else {
        showNotification('Coloca el cursor sobre un carril para pegar', 'error')
      }
    } else {
      showNotification('No hay notas copiadas para pegar', 'error')
    }
  }
}

// Añadir información de atajos de teclado
const shortcuts = [
  { key: 'Ctrl + Click', description: 'Iniciar selección' },
  { key: 'Ctrl + Shift + Click', description: 'Extender selección' },
  { key: 'Ctrl + C', description: 'Copiar notas seleccionadas' },
  { key: 'Ctrl + V', description: 'Pegar notas' },
  { key: 'Ctrl + A', description: 'Seleccionar todo' }
]

// Añadir event listeners en onMounted
onMounted(() => {
  window.addEventListener('keydown', handleKeyboard)
  window.addEventListener('mousemove', updateMousePosition)
})

// Limpiar event listeners en onUnmounted
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyboard)
  window.removeEventListener('mousemove', updateMousePosition)
  if (clickTimeout) {
    clearTimeout(clickTimeout)
  }
})

// Añadir función para manejar click derecho
const handleRightClick = (event) => {
  const editorContainer = event.currentTarget
  const rect = editorContainer.getBoundingClientRect()
  const clickY = event.clientY - rect.top + editorContainer.scrollTop
  
  const newTime = clickY / pixelsPerBeat.value
  
  if (isPlaying.value) {
    audio.value.pause()
    isPlaying.value = false
  }
  
  currentTime.value = newTime
  if (audio.value) {
    audio.value.currentTime = newTime
  }

  showNotification('Playhead movido')
}

// Función helper para mostrar notificaciones
const showNotification = (message, type = 'success') => {
  if (notificationSystem.value) {
    notificationSystem.value.showNotification(message, type)
  }
}

// Añadir función para buscar en el audio
const handleProgressClick = (event, side) => {
  if (!audio.value) return
  
  const container = event.currentTarget
  const rect = container.getBoundingClientRect()
  const clickX = event.clientX - rect.left
  const percentage = clickX / rect.width

  let newTime
  if (side === 'left') {
    newTime = (percentage * 0.5) * duration.value
  } else {
    newTime = (0.5 + percentage * 0.5) * duration.value
  }

  currentTime.value = Math.min(newTime, duration.value)
  audio.value.currentTime = currentTime.value
}
</script>

<style scoped>
.bg-dark {
  background-color: #0e0e0e;
  background-image: url('/imgs/default2.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.map-config-item {
  @apply space-y-1;
}

.config-input {
  @apply w-full px-3 py-2
         bg-purple-900/30 rounded-lg
         border border-purple-500/30
         text-white text-sm
         focus:outline-none focus:ring-1
         focus:ring-purple-500/50
         focus:border-purple-500/50
         placeholder-white/30;
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

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out forwards;
}

/* Aplicar delay a cada item de configuración */
.map-config-item:nth-child(1) { animation-delay: 0.1s; }
.map-config-item:nth-child(2) { animation-delay: 0.2s; }
.map-config-item:nth-child(3) { animation-delay: 0.3s; }
.map-config-item:nth-child(4) { animation-delay: 0.4s; }
.map-config-item:nth-child(5) { animation-delay: 0.5s; }

.editor-container {
  @apply relative overflow-y-auto;
  height: calc(100vh - 13rem); /* Ajustado para dejar espacio para los receptores */
  margin-bottom: 4rem; /* Espacio para los receptores */
}

.fixed-receptors {
  @apply absolute bottom-0 left-0 right-0
         bg-gradient-to-t from-black to-transparent
         pb-4 pt-8;
  z-index: 10;
  @apply space-y-8;
}

.lane-receptor {
  @apply h-16
         flex items-center justify-center
         border-t-2 border-purple-500;
}

.lane-column {
  @apply relative flex-1 border-r border-purple-500/30 
         cursor-crosshair;
  min-height: 100%;
}

.lane-column:first-child {
  @apply border-l;
}

.beat-grid {
  @apply absolute inset-0 pointer-events-none;
}

.beat-line {
  @apply absolute w-full left-0 right-0;
}

.beat-line.main {
  @apply h-0.5 bg-purple-500/60;
}

.beat-line.half {
  @apply h-px bg-purple-500/40;
}

.beat-line.quarter {
  @apply h-px bg-purple-500/30;
}

.beat-line.eighth {
  @apply h-px bg-purple-500/20;
}

/* Hacer las líneas más visibles en hover */


/* Añadir transición suave */
.beat-line {
  transition: opacity 0.2s, background-color 0.2s;
}

.note-wrapper {
  @apply absolute w-full transition-transform;
  transform: translateY(-50%);
  user-select: none;
}

.note-wrapper.conflicting {
  z-index: 10;
}

.note-wrapper.conflicting .note-block {
  @apply border-red-500 from-red-500/60 to-red-700/60;
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

.note-block {
  @apply w-full h-8 
         bg-gradient-to-r from-purple-600 to-pink-500
         border-2 border-purple-400/50
         rounded-md
         shadow-[0_0_10px_rgba(168,85,247,0.4)]
         transition-all duration-150;
}

/* Efecto hover */
.note-block:hover {
  @apply from-pink-400/70 to-purple-400/70
         scale-105 border-pink-400;
}

.highlight-error .note-block {
  @apply border-red-500 from-red-500/60 to-red-700/60;
  animation: shake 0.5s ease-in-out;
}

.playhead {
  @apply absolute left-0 right-0 h-0.5
         bg-gradient-to-r from-pink-500 via-white to-purple-500
         shadow-[0_0_8px_rgba(255,255,255,0.8)]
         z-50;
}

.snap-line {
  @apply absolute w-full h-px;
  pointer-events: none;
}

.snap-beat {
  @apply bg-purple-500/40 h-0.5;
}

.snap-half {
  @apply bg-purple-500/30 h-px;
}

.snap-division {
  @apply bg-purple-500/20 h-px;
}

/* Mostrar líneas de snap al hover sobre el carril */
.lane-column:hover .snap-line {
  opacity: 1;
}

.snap-line {
  opacity: 0.5;
  transition: opacity 0.2s;
}

.snap-button {
  @apply px-3 py-2 
         bg-purple-900/30 
         border border-purple-500/30
         text-white/70 
         rounded-lg
         transition-all duration-200
         hover:bg-purple-800/40
         hover:border-purple-500/50
         hover:text-white;
}

.snap-button.active {
  @apply bg-purple-700/50
         border-purple-500/70
         text-white
         shadow-[inset_0_0_10px_rgba(168,85,247,0.3)];
}

/* Visualización del snap */
.snap-preview {
  @apply flex justify-between items-center h-8 px-2;
}

.snap-marker {
  @apply w-0.5 h-full bg-purple-500/30 transition-all duration-200;
}

.snap-marker.visible {
  @apply bg-purple-500/70;
}

/* Estilo para el error visual */
.placement-error {
  @apply absolute px-4 py-2 
         bg-red-500 text-white rounded-lg
         text-sm font-bold shadow-lg
         z-50;
  transform: translate(-50%, -50%);
  animation: shake 0.5s ease-in-out, fadeOut 1s ease-out;
  pointer-events: none;
  white-space: nowrap;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-4px); }
  75% { transform: translateX(4px); }
}

@keyframes fadeOut {
  0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
  100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
}

/* Mostrar área válida al hover */
.lane-column:hover::after {
  content: '';
  @apply absolute w-full bg-purple-500/10;
  height: v-bind(noteHeight * 2 + 24 + 'px'); /* Aumentar área de visualización */
  pointer-events: none;
}

/* Resaltar líneas de snap válidas */
.beat-line.valid-snap {
  @apply bg-purple-500/40;
}

.error-container {
  @apply fixed z-50;
  pointer-events: none;
}

.error-message {
  @apply fixed px-3 py-2 
         bg-red-500 text-white rounded-lg
         text-sm font-bold shadow-lg
         z-50 whitespace-nowrap;
  transform: translate(-50%, -100%);
  animation: fadeInOut 2s ease-in-out forwards;
}

@keyframes fadeInOut {
  0% { opacity: 0; transform: translate(-50%, -90%); }
  15% { opacity: 1; transform: translate(-50%, -100%); }
  85% { opacity: 1; transform: translate(-50%, -100%); }
  100% { opacity: 0; transform: translate(-50%, -90%); }
}

/* Transiciones para mensajes de error */
.error-enter-active,
.error-leave-active {
  transition: all 0.3s ease;
}

.error-enter-from,
.error-leave-to {
  opacity: 0;
  transform: translate(-50%, -50%) scale(0.9);
}

/* Scrollbar personalizada */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: theme('colors.purple.500') theme('colors.black');
}

.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  @apply bg-black/50;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-purple-500/50 rounded-full;
  border: 2px solid theme('colors.black');
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  @apply bg-purple-500;
}

/* Input Range personalizado */
.custom-range-container {
  @apply relative h-10 cursor-pointer;
}

.range-track {
  @apply absolute top-1/2 left-0 w-full h-2 rounded-full 
         bg-purple-900/30 border-2 border-purple-500/30;
  transform: translateY(-50%);
}

.range-progress {
  @apply absolute top-0 left-0 h-full rounded-full
         bg-gradient-to-r from-pink-500 to-purple-500;
}

.range-thumb {
  @apply absolute top-1/2 w-6 h-6 rounded-full 
         bg-gradient-to-br from-pink-400 to-purple-600
         border-2 border-white/50 cursor-grab;
  transform: translate(-50%, -50%);
  box-shadow: 
    0 0 10px theme('colors.pink.500'),
    0 0 20px theme('colors.purple.500');
}

.range-thumb.dragging {
  @apply cursor-grabbing;
  box-shadow: 
    0 0 15px theme('colors.pink.500'),
    0 0 30px theme('colors.purple.500');
}

.range-thumb:hover {
  box-shadow: 
    0 0 15px theme('colors.pink.500'),
    0 0 30px theme('colors.purple.500');
}

.range-value {
  @apply absolute -top-8 left-1/2 px-2 py-1
         bg-purple-900/90 rounded text-white text-sm
         border border-purple-500/50 whitespace-nowrap;
  transform: translateX(-50%);
}

/* Prevenir selección de texto durante el arrastre */
.custom-range-container {
  user-select: none;
}

/* Checkbox personalizado */
.custom-checkbox {
  @apply appearance-none w-4 h-4 rounded 
         bg-black/30 border-2 border-purple-500/30
         checked:bg-purple-500 checked:border-purple-500
         focus:outline-none focus:ring-2 focus:ring-purple-500/50
         transition-all duration-200 cursor-pointer;
}

/* Efecto glow para botones activos */
.shadow-glow-purple {
  box-shadow: 0 0 15px theme('colors.purple.500');
}

/* Asegurarnos de que todos los elementos que usaban font-game ahora usen la fuente directamente */
.game-text {
  font-family: 'GameFont', sans-serif;
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

.selection-area {
  @apply absolute left-0 right-0
         bg-purple-500/20 border border-purple-500/40
         pointer-events-none z-10;
}

.note-wrapper.selected .note-block {
  @apply ring-2 ring-pink-500 ring-offset-1 ring-offset-purple-900
         shadow-[0_0_10px_rgba(236,72,153,0.5)];
}

.note-wrapper.dragging .note-block {
  @apply scale-105;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateX(20px);
  opacity: 0;
}

/* Actualizar estilos para ser más compactos */
.map-config-item {
  @apply space-y-1;
}

.config-input {
  @apply w-full px-3 py-2;
}

.snap-button {
  @apply px-2 py-1 text-xs;
}

.custom-range-container {
  @apply h-10;
}

/* Personalizar scrollbar sin usar el plugin */
.col-span-3 {
  scrollbar-width: thin; /* Para Firefox */
  scrollbar-color: rgba(168, 85, 247, 0.5) rgba(88, 28, 135, 0.3); /* Para Firefox */
}

/* Para Webkit (Chrome, Safari, Edge) */
.col-span-3::-webkit-scrollbar {
  width: 8px;
}

.col-span-3::-webkit-scrollbar-track {
  background: rgba(88, 28, 135, 0.3);
  border-radius: 4px;
}

.col-span-3::-webkit-scrollbar-thumb {
  background: rgba(168, 85, 247, 0.5);
  border-radius: 4px;
}

.col-span-3::-webkit-scrollbar-thumb:hover {
  background: rgba(168, 85, 247, 0.7);
}

.audio-control-btn {
  @apply p-2 rounded-full
         bg-black/80 
         text-white/70
         hover:bg-purple-800/50
         hover:text-white
         transition-all duration-200
         border border-purple-500/30
         hover:border-purple-500/50
         shadow-[0_0_15px_rgba(168,85,247,0.3)]
         hover:scale-110
         backdrop-blur-sm;
}

.audio-progress-container {
  @apply h-1.5 bg-purple-900/30 
         rounded-full cursor-pointer
         relative overflow-hidden
         border border-purple-500/30;
}

.audio-progress-bar {
  @apply h-full bg-gradient-to-r 
         from-pink-500 to-purple-600
         absolute left-0 top-0
         transition-all duration-100;
}

.progress-complete {
  @apply opacity-50;
}

.play-pause-container {
  @apply z-10 
         flex items-center justify-center
         relative
         -mx-2; /* Ajuste negativo para compensar el padding del botón */
}
</style> 
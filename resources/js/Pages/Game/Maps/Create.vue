<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Header con info básica -->
    <header class="bg-black border-b-2 border-pink-500">
      <div class="container mx-auto px-8 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-8">
            <h1 class="font-game text-4xl">
              <span class="text-white">MAP</span>
              <span class="text-pink-500">/</span>
              <span class="text-purple-500">CREATOR</span>
            </h1>
          </div>
          <div class="flex items-center gap-4">
            <!-- Controles de audio -->
            <button v-if="audioLoaded" 
                    @click="togglePlayback" 
                    class="editor-button">
              {{ isPlaying ? '⏸️ Pausar' : '▶️ Reproducir' }}
            </button>
            <span v-if="audioLoaded" class="text-white font-mono">
              {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
            </span>
          </div>
        </div>
      </div>
    </header>

    <!-- Editor principal -->
    <div class="flex-1 grid grid-cols-12 h-[calc(100vh-5rem)]">
      <!-- Timeline y carriles -->
      <div class="col-span-9 bg-black/80 relative">
        <!-- Receptores fijos en la parte inferior -->
        <div class="fixed-receptors">
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
               :style="{ height: `${duration * pixelsPerBeat}px` }">
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
                     :style="{ 
                       top: `${note.time * pixelsPerBeat}px`,
                     }"
                     @click.stop="removeNote(note)">
                  <div class="note-block"></div>
                </div>
              </div>
            </div>

            <!-- Playhead -->
            <div class="playhead" 
                 :style="{ top: `${currentTime * pixelsPerBeat}px` }">
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
      <div class="col-span-3 bg-black border-l border-purple-900 p-6">
        <div class="space-y-6">
          <!-- Info básica -->
          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Título</label>
            <input v-model="formData.title" type="text" class="config-input">
          </div>

          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Artista</label>
            <input v-model="formData.artist" type="text" class="config-input">
          </div>

          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">BPM</label>
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

          <!-- Input Range personalizado -->
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
                <div class="range-progress" :style="{ width: `${progressPercentage}%` }"></div>
              </div>
              <!-- Thumb -->
              <div class="range-thumb" 
                   :style="{ left: `${progressPercentage}%` }"
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

          <!-- Teclas para mapeo -->
          <div class="map-config-item">
            <label class="text-white font-game mb-2 block">Teclas para mapeo</label>
            <div class="grid grid-cols-4 gap-2 text-center">
              <div v-for="key in ['D','F','J','K']" :key="key"
                   class="bg-purple-900/30 p-2 rounded-lg border border-purple-500/30">
                <span class="text-white">{{ key }}</span>
              </div>
            </div>
            <p class="text-sm text-gray-400 mt-2">
              Usa estas teclas o click para colocar notas
            </p>
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
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import axios from 'axios'
import Spinner from '@/Components/Spinner.vue'
import { router } from '@inertiajs/vue3'

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
  return ((scrollSpeed.value - minValue) / (maxValue - minValue)) * 100
})

const notifications = ref([])
const isSaving = ref(false)

function handleAudioUpload(event) {
  const file = event.target.files[0]
  if (file) {
    // Validar tipo de archivo
    const validTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg']
    if (!validTypes.includes(file.type)) {
      showNotification('Formato de audio no válido. Use MP3, WAV o OGG', 'error')
      event.target.value = '' // Limpiar input
      return
    }

    // Validar tamaño (10MB máximo)
    const maxSize = 10 * 1024 * 1024 // 10MB en bytes
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
  if (!isDragging.value) return
  
  const rect = rangeContainer.value.getBoundingClientRect()
  const percentage = Math.max(0, Math.min(100, 
    ((event.clientX - rect.left) / rect.width) * 100
  ))
  
  // Actualizar inmediatamente el valor
  requestAnimationFrame(() => {
    scrollSpeed.value = Math.round(
      minValue + (percentage / 100) * (maxValue - minValue)
    )
  })
}

// Detener el arrastre
function stopDragging() {
  isDragging.value = false
}

async function saveMap() {
  try {
    // Validaciones básicas
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
      showNotification('¡Mapa guardado con éxito!', 'success')
      
      // Esperar un momento para que se vea la notificación
      setTimeout(() => {
        // Redirigir al listado de mapas
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
      showNotification(
        error.response?.data?.message || 'Error al guardar el mapa',
        'error'
      )
    }
  } finally {
    // Desactivar spinner
    isSaving.value = false
  }
}

function showNotification(message, type = 'success') {
  const id = Date.now()
  notifications.value.push({
    id,
    message,
    type,
    progress: 100
  })

  // Iniciar countdown
  const duration = 5000
  const interval = 10
  const step = (100 * interval) / duration

  const progressInterval = setInterval(() => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.progress -= step
    }
  }, interval)

  setTimeout(() => {
    clearInterval(progressInterval)
    notifications.value = notifications.value.filter(n => n.id !== id)
  }, duration)
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
  @apply animate-fadeIn;
}

.config-input {
  @apply w-full bg-purple-900/30 
         border border-purple-500/40 
         rounded-lg px-4 py-2 
         text-white transition-all
         focus:border-pink-500/50 
         focus:outline-none
         focus:ring-2 
         focus:ring-pink-500/20;
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
}

.lane-receptor {
  @apply h-16
         flex items-center justify-center
         border-t-2 border-purple-500;
}

.lane-column {
  @apply relative flex-1 border-r border-purple-500/30 
         hover:bg-purple-900/20 transition-colors
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
.lane-column:hover .beat-line.main {
  @apply bg-purple-500/60;
}

.lane-column:hover .beat-line.half {
  @apply bg-purple-500/40;
}

.lane-column:hover .beat-line.quarter {
  @apply bg-purple-500/25;
}

.lane-column:hover .beat-line.eighth {
  @apply bg-purple-500/15;
}

/* Añadir transición suave */
.beat-line {
  transition: opacity 0.2s, background-color 0.2s;
}

.note-wrapper {
  @apply absolute w-full;
  transform: translateY(-50%);
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
  @apply mx-auto w-4/5 
         bg-gradient-to-r from-pink-500/60 to-purple-500/60
         border-2 border-pink-500 rounded-lg
         transition-all duration-200;
  height: v-bind(noteHeight + 'px');
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
  @apply absolute w-full h-0.5 bg-white/50;
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
  @apply px-3 py-2 rounded-lg
         bg-purple-900/30 text-white
         border border-purple-500/30
         transition-all duration-200
         hover:bg-purple-900/50
         text-sm;
  font-family: 'GameFont', sans-serif;
}

.snap-button.active {
  @apply bg-pink-500/30 border-pink-500
         shadow-[0_0_10px_rgba(236,72,153,0.3)];
}

.snap-preview {
  @apply h-8 relative flex items-center
         border-l-2 border-r-2 border-purple-500/30;
}

.snap-marker {
  @apply absolute h-full w-px bg-purple-500/30
         opacity-0 transition-opacity duration-200;
}

.snap-marker.visible {
  @apply opacity-100;
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
  @apply relative h-12 cursor-pointer;
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
</style> 
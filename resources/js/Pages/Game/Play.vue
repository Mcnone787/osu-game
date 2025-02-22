<template>
  <div class="min-h-screen bg-dark flex flex-col">
    <!-- Header -->
    <header class="bg-black border-b-2 border-pink-500">
      <div class="container mx-auto px-8 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-8">
            <h1 class="font-game text-4xl">
              <span class="text-white">RHYTHM</span>
              <span class="text-pink-500">/</span>
              <span class="text-purple-500">MASTER</span>
            </h1>
          </div>
          <div class="flex items-center gap-6">
            <span class="text-gray-400 font-game">Playing as</span>
            <span class="text-white font-game text-xl">Guest</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <div class="flex-1 grid grid-cols-12 h-[calc(100vh-5rem)]">
      <!-- Ranking Panel (Izquierda) -->
      <div class="col-span-9 bg-black/80 p-8 text-white">
        <div class="ranking-container">
          <div class="mb-6">
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
      <div class="col-span-3 bg-black border-l border-purple-900 overflow-y-auto text-white">
        <div class="song-list">
          <div v-for="song in songs" 
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import DropdownSelector from '@/Components/DropdownSelector.vue'
import { GlobeAltIcon, UserGroupIcon, FlagIcon } from '@heroicons/vue/24/outline'
import { markRaw } from 'vue'

const props = defineProps({
  songs: {
    type: Array,
    default: () => []
  }
})

const selectedSong = ref(null)

// Tipos de ranking disponibles
const rankingTypes = [
  { id: 'global', name: 'Global Ranking', icon: markRaw(GlobeAltIcon) },
  { id: 'friends', name: 'Friends Ranking', icon: markRaw(UserGroupIcon) },
  { id: 'country', name: 'Country Ranking', icon: markRaw(FlagIcon) }
]

// Datos de ejemplo para cada tipo de ranking
const mockRankings = {
  global: [
    {
      position: 1,
      player: "★ MasterRhythm ★",
      score: 1234567,
      accuracy: 99.87,
      combo: "1,458",
      date: "2024-03-15",
      avatar: "/imgs/avatars/player1.jpg",
      level: 99
    },
    {
      position: 2,
      player: "BeatMaster_Pro",
      score: 1198234,
      accuracy: 98.92,
      combo: "1,445",
      date: "2024-03-14",
      avatar: "/imgs/avatars/player2.jpg",
      level: 87
    },
    {
      position: 3,
      player: "RhythmKing",
      score: 1165432,
      accuracy: 98.45,
      combo: "1,432",
      date: "2024-03-13",
      avatar: "/imgs/avatars/player3.jpg",
      level: 92
    },
    {
      position: 4,
      player: "MusicNinja",
      score: 1132789,
      accuracy: 97.89,
      combo: "1,421",
      date: "2024-03-12",
      avatar: "/imgs/avatars/player4.jpg",
      level: 85
    },
    {
      position: 5,
      player: "BeatSaber",
      score: 1098765,
      accuracy: 97.34,
      combo: "1,398",
      date: "2024-03-11",
      avatar: "/imgs/avatars/player5.jpg",
      level: 78
    }
  ],
  friends: [
    {
      position: 1,
      player: "BestFriend123",
      score: 1198234,
      accuracy: 98.92,
      combo: "1,445",
      date: "2024-03-14",
      avatar: "/imgs/avatars/player2.jpg",
      level: 85
    },
  ],
  country: [
    {
      position: 1,
      player: "LocalChampion",
      score: 1165432,
      accuracy: 98.45,
      combo: "1,432",
      date: "2024-03-13",
      avatar: "/imgs/avatars/player3.jpg",
      level: 92
    },
  ]
}

const selectedRanking = ref(rankingTypes[0])
const currentRankings = computed(() => mockRankings[selectedRanking.value.id])

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
  @apply p-2;
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
</style>


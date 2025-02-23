<template>
  <div class="min-h-screen bg-dark bg-cover bg-center bg-no-repeat flex flex-col">
    <!-- Usar el componente Header existente -->
    <GameHeader title="Perfil" />

    <!-- Main Content -->
    <main class="flex-1 container mx-auto px-4 py-8">
      <!-- Perfil Header -->
      <div class="bg-black/80 rounded-xl p-8 border border-purple-500/30 shadow-lg mb-8">
        <div class="flex items-center gap-8">
          <!-- Avatar -->
          <div class="relative w-32 h-32">
            <div class="absolute inset-0 rounded-full bg-gradient-to-r from-pink-500 to-purple-600"></div>
            <img 
              :src="user.avatar || '/default-avatar.png'" 
              class="relative w-full h-full rounded-full border-4 border-purple-500/50 object-cover"
              alt="Avatar"
            >
          </div>

          <!-- Info básica -->
          <div class="flex-1">
            <h1 class="font-game text-4xl text-white mb-2">{{ user.name }}</h1>
            <p class="text-purple-400">{{ user.email }}</p>
            <div class="flex gap-4 mt-4">
              <div class="stat-box">
                <span class="stat-value">{{ stats.totalGames || 0 }}</span>
                <span class="stat-label">Partidas</span>
              </div>
              <div class="stat-box">
                <span class="stat-value">{{ stats.accuracy || '0%' }}</span>
                <span class="stat-label">Precisión</span>
              </div>
              <div class="stat-box">
                <span class="stat-value">{{ stats.rank || 'N/A' }}</span>
                <span class="stat-label">Ranking</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Estadísticas Detalladas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Gráfico de Rendimiento -->
        <div class="stats-card">
          <h2 class="card-title">Rendimiento</h2>
          <div class="h-64 bg-purple-900/20 rounded-lg border border-purple-500/30">
            <!-- Aquí iría el componente de gráfico -->
          </div>
        </div>

        <!-- Logros -->
        <div class="stats-card">
          <h2 class="card-title">Logros</h2>
          <div class="grid grid-cols-3 gap-4">
            <div v-for="achievement in achievements" :key="achievement.id" 
                 class="achievement-box"
                 :class="{ 'opacity-40': !achievement.unlocked }">
              <div class="achievement-icon">
                <component :is="achievement.icon" class="w-8 h-8"/>
              </div>
              <span class="text-sm mt-2">{{ achievement.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Historial de Partidas -->
      <div class="stats-card">
        <h2 class="card-title">Historial de Partidas</h2>
        <div class="space-y-4">
          <div v-for="game in recentGames" :key="game.id" 
               class="game-history-item">
            <div class="flex items-center gap-4">
              <img :src="game.songImage" class="w-16 h-16 rounded object-cover" alt="Song cover">
              <div class="flex-1">
                <h3 class="text-white font-game">{{ game.songName }}</h3>
                <p class="text-purple-400 text-sm">{{ game.date }}</p>
              </div>
              <div class="text-right">
                <p class="text-2xl font-game" 
                   :class="game.score > 900000 ? 'text-pink-400' : 'text-white'">
                  {{ game.score }}
                </p>
                <p class="text-sm text-purple-400">{{ game.accuracy }}% Precisión</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="w-full px-5 py-3 bg-black/50 text-white">
      <p class="font-game text-sm text-right">
        Created by Adria Moya
      </p>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import GameHeader from '@/Components/GameHeader.vue' // Importar el componente Header

// Datos de ejemplo
const user = ref({
  name: 'Usuario Ejemplo',
  email: 'usuario@ejemplo.com',
  avatar: null
})

const stats = ref({
  totalGames: 156,
  accuracy: '95.4%',
  rank: '#1337'
})

const achievements = ref([
  { id: 1, name: 'Primer S', icon: 'TrophyIcon', unlocked: true },
  { id: 2, name: 'Combo 1000x', icon: 'FireIcon', unlocked: true },
  { id: 3, name: 'Pro Player', icon: 'StarIcon', unlocked: false },
  // ... más logros
])

const recentGames = ref([
  {
    id: 1,
    songName: 'Through the Fire and Flames',
    songImage: '/song1.jpg',
    score: 985420,
    accuracy: 98.5,
    date: '2024-03-15'
  },
  // ... más partidas
])

onMounted(() => {
  // Aquí irían las llamadas a la API para obtener los datos reales
})
</script>

<style scoped>
.stat-box {
  @apply bg-purple-900/30 px-6 py-3 rounded-lg border border-purple-500/30
         flex flex-col items-center transition-all duration-200
         hover:bg-purple-800/40 hover:border-purple-500/50;
}

.stat-value {
  @apply text-2xl font-game text-white;
}

.stat-label {
  @apply text-sm text-purple-400;
}

.stats-card {
  @apply bg-black/80 rounded-xl p-6 border border-purple-500/30 shadow-lg;
}

.card-title {
  @apply font-game text-2xl text-white mb-4;
}

.achievement-box {
  @apply flex flex-col items-center p-4 bg-purple-900/30 rounded-lg
         border border-purple-500/30 text-white text-center
         transition-all duration-200
         hover:bg-purple-800/40 hover:border-purple-500/50;
}

.achievement-icon {
  @apply w-12 h-12 rounded-full bg-gradient-to-r from-pink-500 to-purple-600
         flex items-center justify-center text-white;
}

.game-history-item {
  @apply bg-purple-900/30 p-4 rounded-lg border border-purple-500/30
         transition-all duration-200
         hover:bg-purple-800/40 hover:border-purple-500/50;
}
</style>
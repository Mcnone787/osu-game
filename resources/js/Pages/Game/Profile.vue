<template>
    <div class="min-h-screen bg-dark bg-cover bg-center bg-no-repeat flex flex-col">
        <!-- Usar el componente Header existente -->
        <GameHeader title="Perfil" />

        <!-- Main Content -->
        <main class="flex-1 container mx-auto px-4 py-8">
            <!-- Perfil Header -->
            <div class="bg-black/80 rounded-xl p-8 border border-purple-500/30 shadow-lg mb-8 relative overflow-hidden">
                <!-- Efecto de brillo en el borde -->
                <div class="absolute inset-0 border-2 border-purple-500/20 rounded-xl"></div>
                <div class="absolute -inset-1 bg-gradient-to-r from-purple-500/10 to-pink-500/10 blur-xl"></div>

                <div class="relative flex items-center gap-8">
                    <!-- Avatar con efectos -->
                    <div class="relative w-32 h-32 group">
                        <!-- Fondo con gradiente animado -->
                        <div class="absolute inset-0 rounded-full bg-gradient-to-r from-pink-500 to-purple-600 animate-gradient"></div>
                        
                        <!-- Imagen -->
                        <img 
                            :src="isEditing && avatarPreview ? avatarPreview : (user.avatar || '/imgs/avatars/default.jpg')" 
                            class="relative w-full h-full rounded-full border-4 border-purple-500/50 object-cover transition-transform duration-300 group-hover:scale-105"
                            alt="Avatar"
                        >
                        
                        <!-- Botón de edición de avatar -->
                        <label v-if="isEditing" 
                               class="absolute bottom-0 right-0 bg-purple-500 rounded-full p-2.5 cursor-pointer
                                      hover:bg-purple-600 transition-all duration-300 transform hover:scale-110
                                      shadow-lg hover:shadow-purple-500/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <input type="file" class="hidden" @change="onAvatarChange" accept="image/*">
                        </label>
                    </div>

                    <!-- Info básica -->
                    <div class="flex-1">
                        <div v-if="!isEditing" class="relative group">
                            <h1 class="font-game text-4xl text-white mb-2 group-hover:text-purple-400 transition-colors duration-300">
                                {{ user.name }}
                                <button @click="startEditing" 
                                        class="ml-3 text-purple-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300
                                               hover:text-purple-400 focus:outline-none">
                                    <i class="fas fa-pencil-alt text-xl"></i>
                                </button>
                            </h1>
                            <p class="text-purple-400 group-hover:text-purple-300 transition-colors duration-300">{{ user.email }}</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div class="relative">
                                <label class="block text-sm font-medium text-purple-400 mb-1">Nombre</label>
                                <input type="text" v-model="form.name" 
                                       class="mt-1 block w-full rounded-lg bg-purple-900/30 border-purple-500/30 text-white 
                                              focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50
                                              transition-all duration-200 pl-10">
                                <i class="fas fa-user absolute left-3 top-[2.4rem] text-purple-400"></i>
                                <div v-if="form.errors.name" class="text-pink-500 text-sm mt-1">{{ form.errors.name }}</div>
                            </div>
                            <div class="relative">
                                <label class="block text-sm font-medium text-purple-400 mb-1">Email</label>
                                <input type="email" v-model="form.email" 
                                       class="mt-1 block w-full rounded-lg bg-purple-900/30 border-purple-500/30 text-white 
                                              focus:border-purple-500 focus:ring focus:ring-purple-500 focus:ring-opacity-50
                                              transition-all duration-200 pl-10">
                                <i class="fas fa-envelope absolute left-3 top-[2.4rem] text-purple-400"></i>
                                <div v-if="form.errors.email" class="text-pink-500 text-sm mt-1">{{ form.errors.email }}</div>
                            </div>
                        </div>

                        <!-- Estadísticas con hover effects -->
                        <div class="flex gap-4 mt-6">
                            <div class="stat-box group">
                                <span class="stat-icon">
                                    <i class="fas fa-gamepad text-2xl text-purple-400 group-hover:text-purple-300 transition-colors duration-300"></i>
                                </span>
                                <span class="stat-value group-hover:text-purple-300 transition-colors duration-300">{{ stats.totalGames }}</span>
                                <span class="stat-label group-hover:text-purple-300 transition-colors duration-300">Partidas</span>
                            </div>
                            <div class="stat-box group">
                                <span class="stat-icon">
                                    <i class="fas fa-bullseye text-2xl text-purple-400 group-hover:text-purple-300 transition-colors duration-300"></i>
                                </span>
                                <span class="stat-value group-hover:text-purple-300 transition-colors duration-300">{{ stats.accuracy }}</span>
                                <span class="stat-label group-hover:text-purple-300 transition-colors duration-300">Precisión</span>
                            </div>
                            <div class="stat-box group">
                                <span class="stat-icon">
                                    <i class="fas fa-trophy text-2xl text-purple-400 group-hover:text-purple-300 transition-colors duration-300"></i>
                                </span>
                                <span class="stat-value group-hover:text-purple-300 transition-colors duration-300">{{ stats.rank }}</span>
                                <span class="stat-label group-hover:text-purple-300 transition-colors duration-300">Ranking</span>
                            </div>
                        </div>

                        <!-- Botones de edición -->
                        <div class="mt-6 flex gap-4">
                            <button v-if="!isEditing"
                                    @click="startEditing" 
                                    class="px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 
                                           focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 
                                           transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-purple-500/50
                                           font-game flex items-center gap-2">
                                <i class="fas fa-pencil-alt"></i>
                                Editar Perfil
                            </button>
                            <template v-else>
                                <button @click="saveChanges" 
                                        class="px-6 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 
                                               focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 
                                               transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-purple-500/50
                                               font-game flex items-center gap-2"
                                        :disabled="form.processing">
                                    <i class="fas fa-save"></i>
                                    Guardar
                                </button>
                                <button @click="cancelEditing" 
                                        class="px-6 py-2 bg-purple-900/50 text-white rounded-lg hover:bg-purple-900/70 
                                               focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-50 
                                               transition-all duration-300 transform hover:scale-105
                                               font-game flex items-center gap-2">
                                    <i class="fas fa-times"></i>
                                    Cancelar
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Detalladas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Gráfico de Rendimiento -->
                <div class="stats-card">
                    <h2 class="card-title">Rendimiento</h2>
                    <div v-if="performanceData.length === 0" 
                         class="h-64 bg-purple-900/20 rounded-lg border border-purple-500/30 
                                flex items-center justify-center text-purple-400">
                        No hay suficientes datos para mostrar el rendimiento
                    </div>
                    <div v-else class="h-[400px] relative">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Logros -->
                <div class="stats-card">
                    <h2 class="card-title">Logros</h2>
                    <div class="h-64 bg-purple-900/20 rounded-lg border border-purple-500/30 
                                flex items-center justify-center text-purple-400">
                        Próximamente...
                    </div>
                </div>
            </div>

            <!-- Historial de Partidas -->
            <div class="stats-card">
                <h2 class="card-title">Historial de Partidas</h2>
                <div class="space-y-4">
                    <div v-if="recentGames.length === 0" 
                         class="text-center py-8 text-purple-400">
                        No hay partidas registradas aún
                    </div>
                    <div v-else v-for="game in recentGames" :key="game.id" 
                         class="game-history-item">
                        <div class="flex items-center gap-4">
                            <img :src="game.songImage" class="w-16 h-16 rounded object-cover" :alt="game.songName">
                            <div class="flex-1">
                                <h3 class="text-white font-game">{{ game.songName }}</h3>
                                <p class="text-purple-400 text-sm">{{ game.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-game" 
                                   :class="game.score > 900000 ? 'text-pink-400' : 'text-white'">
                                    {{ Number(game.score).toLocaleString() }}
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
import { ref, computed } from 'vue'
import { Line } from 'vue-chartjs'
import { useForm } from '@inertiajs/vue3'
import GameHeader from '@/Components/GameHeader.vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps({
    user: Object,
    stats: Object,
    recentGames: Array,
    performanceData: Array
})

const isEditing = ref(false)
const avatarPreview = ref(null)

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    avatar: null
})

const startEditing = () => {
    isEditing.value = true
}

const cancelEditing = () => {
    isEditing.value = false
    form.reset()
    form.clearErrors()
    avatarPreview.value = null
}

const saveChanges = () => {
    form.post(route('game.profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false
            avatarPreview.value = null
        }
    })
}

const onAvatarChange = (e) => {
    const file = e.target.files[0]
    if (file) {
        form.avatar = file
        avatarPreview.value = URL.createObjectURL(file)
    }
}

const chartData = computed(() => ({
    labels: props.performanceData.map(data => data.date),
    datasets: [
        {
            label: 'Puntuación',
            data: props.performanceData.map(data => data.score),
            borderColor: '#ec4899',
            backgroundColor: '#ec489922',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#ec4899',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
        },
        {
            label: 'Precisión',
            data: props.performanceData.map(data => data.accuracy),
            borderColor: '#a855f7',
            backgroundColor: '#a855f722',
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#a855f7',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
        }
    ]
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
        mode: 'index',
        intersect: false,
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: '#ffffff22'
            },
            ticks: {
                color: '#ffffff88'
            }
        },
        x: {
            grid: {
                color: '#ffffff22'
            },
            ticks: {
                color: '#ffffff88'
            }
        }
    },
    plugins: {
        legend: {
            labels: {
                color: '#ffffff',
                font: {
                    size: 14,
                    weight: 'bold'
                },
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            titleFont: {
                size: 14,
                weight: 'bold'
            },
            bodyFont: {
                size: 13
            },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
                title: (tooltipItems) => {
                    const dataIndex = tooltipItems[0].dataIndex;
                    return props.performanceData[dataIndex].songName;
                },
                label: (context) => {
                    const value = context.raw;
                    const label = context.dataset.label;
                    return `${label}: ${value}`;
                }
            }
        }
    }
}
</script>

<style scoped>
.stat-box {
    @apply bg-purple-900/30 px-6 py-4 rounded-lg border border-purple-500/30
           flex flex-col items-center transition-all duration-300
           hover:bg-purple-800/40 hover:border-purple-500/50 hover:transform hover:scale-105
           hover:shadow-lg hover:shadow-purple-500/20;
}

.stat-icon {
    @apply mb-2;
}

.stat-value {
    @apply text-2xl font-game text-white;
}

.stat-label {
    @apply text-sm text-purple-400;
}

.stats-card {
    @apply bg-black/80 rounded-xl p-6 border border-purple-500/30 shadow-lg
           hover:border-purple-500/50 transition-all duration-300;
}

.card-title {
    @apply font-game text-2xl text-white mb-4;
}

.game-history-item {
    @apply bg-purple-900/30 p-4 rounded-lg border border-purple-500/30
           transition-all duration-300
           hover:bg-purple-800/40 hover:border-purple-500/50 hover:transform hover:scale-[1.02]
           hover:shadow-lg hover:shadow-purple-500/20;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 8s ease infinite;
}
</style>
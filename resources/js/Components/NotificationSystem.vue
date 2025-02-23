<template>
  <div class="fixed top-4 right-4 z-[100] space-y-4 min-w-[300px]">
    <div v-for="notification in notifications" 
         :key="notification.id"
         class="notification-container"
         :class="notification.type">
      <div class="flex items-center gap-3">
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
        <span>{{ notification.message }}</span>
      </div>
      <!-- Barra de progreso -->
      <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-pink-500 to-purple-600 rounded-b-lg"
           :style="{ width: `${notification.progress}%` }">
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const notifications = ref([])

const showNotification = (message, type = 'success') => {
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

  // Eliminar notificación después de la duración
  setTimeout(() => {
    clearInterval(progressInterval)
    notifications.value = notifications.value.filter(n => n.id !== id)
  }, duration)
}

// Exponer el método para usar desde otros componentes
defineExpose({ showNotification })
</script>

<style scoped>
.notification-container {
  @apply relative bg-black/90 text-white px-4 py-3 rounded-lg
         border border-purple-500/30 shadow-lg
         animate-slideIn;
}

.notification-container.success {
  @apply border-emerald-500/30;
}

.notification-container.error {
  @apply border-red-500/30;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}
</style> 
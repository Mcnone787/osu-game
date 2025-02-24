<template>
  <div class="min-h-screen bg-dark bg-cover bg-center bg-no-repeat flex flex-col justify-between">
    <GameHeader 

      :show-back="false"
      @toggle-settings="isSettingsOpen = true"
    />

    <!-- Panel lateral de configuración/auth -->
    <div class="fixed inset-y-0 left-0 w-[420px] bg-black/95 transform transition-transform duration-300 z-50"
         :class="[isSettingsOpen ? 'translate-x-0' : '-translate-x-full']">
      <div class="h-full" :key="isSettingsOpen">
        <!-- Cabecera del panel -->
        <div class="flex justify-between items-center p-8 border-b border-purple-500/30">
          <h2 class="text-3xl font-game text-white">
            {{ user ? 'Configuración' : (isLogin ? 'Iniciar Sesión' : 'Registrarse') }}
          </h2>
          <button @click="isSettingsOpen = false" 
                  class="text-white/50 hover:text-white transition-colors p-2">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Contenido del panel -->
        <div class="p-8">
          <!-- Menú de configuración para usuarios logueados -->
          <template v-if="user">
            <div class="space-y-6">
              <!-- Info del usuario -->
              <div class="bg-purple-900/30 rounded-lg p-4 border border-purple-500/30">
                <p class="text-white font-game">Usuario</p>
                <p class="text-purple-400">{{ user.name }}</p>
                <p class="text-white/60 text-sm">{{ user.email }}</p>
              </div>

              <!-- Opciones de configuración -->
              <div class="space-y-4">
                <button class="config-button">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>Ajustes de juego</span>
                </button>

                <button class="config-button">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                  </svg>
                  <span>Personalización</span>
                </button>

                <button @click="handleLogout" 
                        class="config-button text-red-400 hover:text-red-300 hover:bg-red-500/20 hover:border-red-500/30">
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                  <span>Cerrar sesión</span>
                </button>
              </div>
            </div>
          </template>

          <!-- Formulario de login/registro para usuarios no logueados -->
          <template v-else>
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Error general si existe -->
              <div v-if="errors.general" 
                   class="bg-red-500/10 border border-red-500/50 rounded-lg p-3">
                <p class="text-red-500 text-sm">{{ errors.general[0] }}</p>
              </div>

              <!-- Campos específicos de registro -->
              <template v-if="!isLogin">
                <div class="space-y-3">
                  <label class="text-white/80 font-game text-base">Nombre de usuario</label>
                  <input type="text" 
                         v-model="form.username" 
                         class="auth-input"
                         :class="{ 'border-red-500': errors.name }"
                         placeholder="Tu nombre de usuario">
                  <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
                </div>
              </template>

              <div class="space-y-3">
                <label class="text-white/80 font-game text-base">Email</label>
                <input type="email" 
                       v-model="form.email" 
                       class="auth-input"
                       :class="{ 'border-red-500': errors.email }"
                       placeholder="tu@email.com">
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
              </div>

              <div class="space-y-3">
                <label class="text-white/80 font-game text-base">Contraseña</label>
                <input type="password" 
                       v-model="form.password" 
                       class="auth-input"
                       :class="{ 'border-red-500': errors.password }"
                       placeholder="••••••••">
                <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
              </div>

              <!-- Confirmación de contraseña solo para registro -->
              <div v-if="!isLogin" class="space-y-3">
                <label class="text-white/80 font-game text-base">Confirmar Contraseña</label>
                <input type="password" 
                       v-model="form.password_confirmation" 
                       class="auth-input"
                       placeholder="••••••••">
              </div>

              <!-- Remember me para login -->
              <div v-if="isLogin" class="flex items-center">
                <input type="checkbox" 
                       v-model="form.remember"
                       class="rounded border-purple-500/30 bg-purple-900/30 text-purple-500">
                <span class="ml-2 text-white/80">Recordarme</span>
              </div>

              <!-- Botón de envío con spinner -->
              <button type="submit" 
                      :disabled="processing"
                      class="w-full py-4 px-6 mt-8 rounded-lg font-game text-lg text-white
                             bg-gradient-to-r from-pink-500 to-purple-600
                             hover:from-pink-600 hover:to-purple-700
                             focus:ring-2 focus:ring-purple-500 focus:ring-offset-2
                             focus:ring-offset-black
                             transition-all duration-200
                             border border-purple-500/50
                             shadow-[0_0_15px_rgba(236,72,153,0.3)]
                             disabled:opacity-90 relative">
                <span :class="{ 'opacity-0': processing }">
                  {{ isLogin ? 'Iniciar Sesión' : 'Registrarse' }}
                </span>
                
                <!-- Spinner estilizado -->
                <div v-if="processing" 
                     class="absolute inset-0 flex items-center justify-center">
                  <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </button>

              <!-- Alternar entre login y registro -->
              <p class="text-center text-white/60 mt-6 text-base">
                {{ isLogin ? '¿No tienes cuenta?' : '¿Ya tienes cuenta?' }}
                <button type="button" 
                        @click="isLogin = !isLogin"
                        class="text-pink-500 hover:text-pink-400 font-game ml-2">
                  {{ isLogin ? 'Regístrate' : 'Inicia sesión' }}
                </button>
              </p>
            </form>
          </template>
        </div>
      </div>
    </div>

    <!-- Overlay de fondo -->
    <div v-if="isSettingsOpen" 
         @click="isSettingsOpen = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40">
    </div>

    <!-- Main Content -->
    <main class="flex-1 flex justify-center items-center p-5">
      <div class="relative flex items-center" 
           :class="{ '-translate-x-[15vw] transition-transform duration-300': isMenuOpen }">
        <!-- Botón BEAT! -->
        <button 
          @click="handleClick"
          :disabled="isAnimating"
          class="osu-button z-10"
        >
          BEAT!
        </button>

        <!-- Menú que aparece al hacer click -->
        <div 
          v-show="isMenuOpen"
          class="menu-container-alt"
        >
          <component 
            :is="item.requiresAuth && !user ? 'button' : 'Link'"
            v-for="(item, index) in menuItems"
            :key="index"
            :href="item.href" 
            class="menu-item-alt opacity-0 cursor-pointer"
            :class="{ 
              'opacity-100': itemVisible[index],
              'translate-y-0': itemVisible[index],
              'translate-y-2': !itemVisible[index]
            }"
            :style="{ 
              width: `${itemWidths[index]}px`,
              display: itemWidths[index] === 0 ? 'none' : 'flex'
            }"
            @click="(e) => handleMenuItemClick(item, e)"
          >
            <span class="relative z-10" >
              {{ item.text }}
            </span>
          </component>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="w-full px-5 py-3 bg-black/50 text-white">
      <p class="font-game text-sm text-right">
        Created by Adria Moya
      </p>
    </footer>

    <!-- Sistema de notificaciones -->
    <NotificationSystem ref="notificationSystem" />
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import GameHeader from '@/Components/GameHeader.vue'
import NotificationSystem from '@/Components/NotificationSystem.vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
const isMenuOpen = ref(false)
const isSettingsOpen = ref(false)
const isAnimating = ref(false)
const itemVisible = ref([false, false, false, false])
const itemWidths = ref([0, 0, 0, 0])
const isAuthPanelOpen = ref(false)
const isLogin = ref(true)
const shouldResetForm = ref(false)
const errors = ref({})
const processing = ref(false)
const notificationSystem = ref(null)

const form = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  remember: false
})

const controls = [
  {
    icon: 'ChevronLeftIcon',
    action: () => {
      console.log('ChevronLeftIcon action')
    }
  },
  {
    icon: 'PauseIcon',
    action: () => {
      console.log('PauseIcon action')
    }
  },
  {
    icon: 'PlayIcon',
    action: () => {
      console.log('PlayIcon action')
    }
  },
  {
    icon: 'ChevronRightIcon',
    action: () => {
      console.log('ChevronRightIcon action')
    }
  }
]

const menuItems = [
  { text: 'Play Now', href: '/game/play' },
  { 
    text: 'Add Map', 
    href: '/maps',
    requiresAuth: true 
  },
  { 
    text: 'Profile', 
    href: '/game/profile',
    requiresAuth: true 
  },
  { text: 'Exit', href: '/game/exit' }
]

const resetStates = () => {
  itemVisible.value = [false, false, false, false]
  itemWidths.value = [0, 0, 0, 0]
}

const animateItem = async (index) => {
  itemWidths.value[index] = 0
  await new Promise(resolve => requestAnimationFrame(resolve))
  itemVisible.value[index] = true
  
  for (let width = 0; width <= 400; width += 25) {
    if (!isMenuOpen.value) break
    itemWidths.value[index] = width
    await new Promise(resolve => requestAnimationFrame(resolve))
  }
  
  if (isMenuOpen.value) {
    itemWidths.value[index] = 400
  }
}

const animateMenuItems = async () => {
  for (let i = 0; i < menuItems.length; i++) {
    if (!isMenuOpen.value) break
    await animateItem(i)
    await new Promise(resolve => setTimeout(resolve, 20))
  }
}

const closeMenu = async () => {
  for (let i = menuItems.length - 1; i >= 0; i--) {
    for (let width = itemWidths.value[i]; width >= 0; width -= 25) {
      itemWidths.value[i] = width
      await new Promise(resolve => requestAnimationFrame(resolve))
    }
    itemWidths.value[i] = 0
    itemVisible.value[i] = false
  }
  
  await new Promise(resolve => setTimeout(resolve, 50))
  isMenuOpen.value = false
}

const handleClick = async () => {
  if (isAnimating.value) return
  
  isAnimating.value = true
  try {
    if (!isMenuOpen.value) {
      isMenuOpen.value = true
      resetStates()
      await animateMenuItems()
    } else {
      await closeMenu()
    }
  } finally {
    isAnimating.value = false
  }
}

function toggleAuthPanel() {
  if (isAuthPanelOpen.value) {
    // Si estamos cerrando el panel
    isAuthPanelOpen.value = false
    // Esperamos a que termine la animación para resetear
    setTimeout(() => {
      if (!isAuthPanelOpen.value) { // Verificamos que sigue cerrado
        form.value = {
          username: '',
          email: '',
          password: '',
          password_confirmation: '',
          remember: false
        }
        isLogin.value = true
      }
    }, 300) // 300ms es la duración de la animación
  } else {
    // Si estamos abriendo el panel
    isAuthPanelOpen.value = true
  }
}

// Limpiar errores al cambiar entre login y registro
watch(isLogin, () => {
  errors.value = {}
})

const handleMenuItemClick = (item) => {
  if (item.requiresAuth && !user.value) {
    notificationSystem.value.showNotification(
      'Necesitas iniciar sesión para acceder a esta sección', 
      'error'
    )
    isSettingsOpen.value = true
    return
  }
  
  // Solo navega si está autenticado o no requiere autenticación
  if (!item.requiresAuth || user.value) {
    router.visit(item.href)
  }
}

async function handleSubmit() {
  processing.value = true
  errors.value = {}
  
  try {
    if (isLogin.value) {
      const response = await axios.post(route('game.login'), {
        email: form.value.email,
        password: form.value.password,
        remember: form.value.remember,
      })
      
      if (response.data.success) {
        isAuthPanelOpen.value = false
        usePage().props.auth.user = response.data.user
        notificationSystem.value.showNotification('¡Bienvenido de nuevo!')
      }
    } else {
      const response = await axios.post(route('game.register'), {
        name: form.value.username,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
      })
      
      if (response.data.success) {
        isAuthPanelOpen.value = false
        usePage().props.auth.user = response.data.user
        notificationSystem.value.showNotification('¡Registro completado! Bienvenido al juego')
      }
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      notificationSystem.value.showNotification('Por favor, verifica los datos ingresados', 'error')
    } else {
      errors.value = {
        general: ['Ha ocurrido un error. Por favor, inténtalo de nuevo.']
      }
      notificationSystem.value.showNotification('Error de conexión. Inténtalo más tarde', 'error')
    }
  } finally {
    processing.value = false
  }
}

async function handleLogout() {
  try {
    const response = await axios.post(route('game.logout'))
    if (response.data.success) {
      usePage().props.auth.user = null
      isSettingsOpen.value = false
      notificationSystem.value.showNotification('¡Hasta pronto! Has cerrado sesión correctamente')
    }
  } catch (error) {
    notificationSystem.value.showNotification('Error al cerrar sesión. Inténtalo de nuevo.', 'error')
  }
}
</script>

<style scoped>
.menu-container-alt {
  @apply absolute left-[85%] top-1/2 -translate-y-1/2
         flex flex-col gap-6;
}

.menu-item-alt {
  @apply px-12 py-3
         rounded-lg text-center text-white text-2xl
         items-center justify-center
         bg-purple-800/30
         border border-purple-500/40;
  font-family: 'GameFont', sans-serif;
  transition: width 0.03s linear,
              opacity 0.15s ease,
              transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  overflow: hidden;
}

.menu-item-alt:hover {
  @apply bg-purple-600/40 border-pink-400/60
         transform scale-[1.01]
         shadow-[0_0_15px_rgba(168,85,247,0.3)];
}

.menu-item-alt span {
  transition: opacity 0.15s;
  white-space: nowrap;
}

.relative {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.osu-button:disabled {
  cursor: pointer;
}

.auth-input {
  @apply w-full px-5 py-3 rounded-lg text-base
         bg-purple-900/30 text-white
         border border-purple-500/30
         focus:ring-2 focus:ring-purple-500/50
         focus:outline-none
         placeholder-white/30
         transition-all duration-200;
}

.auth-input:hover {
  @apply border-purple-500/50
         bg-purple-900/40;
}

/* Separamos los estilos de error para evitar la dependencia circular */
.auth-input.border-red-500 {
  border-color: rgb(239 68 68 / 0.5);
}

.auth-input.border-red-500:focus {
  border-color: rgb(239 68 68);
  --tw-ring-color: rgb(239 68 68 / 0.5);
}

/* Animación suave para el panel */
.transform {
  will-change: transform;
  backface-visibility: hidden;
}

.config-button {
  @apply w-full flex items-center gap-3 px-4 py-3
         bg-purple-900/30 rounded-lg text-white
         border border-purple-500/30
         hover:bg-purple-800/40 hover:border-purple-500/50
         hover:shadow-[0_0_15px_rgba(168,85,247,0.2)]
         transition-all duration-200;
}

.config-button svg {
  @apply text-purple-400;
}

.config-button:hover svg {
  @apply text-pink-400;
}

.notification-container {
  @apply relative overflow-hidden
         bg-black/95 text-white
         px-4 py-3 rounded-lg
         border border-purple-500/30
         shadow-[0_0_15px_rgba(168,85,247,0.2)]
         animate-slide-in;
}

.notification-container.success {
  @apply border-emerald-500/30;
}

.notification-container.error {
  @apply border-red-500/30;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style> 
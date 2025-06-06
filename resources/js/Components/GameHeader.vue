<template>
    <header class="bg-black border-b-2 border-pink-500">
      <div class="container mx-auto px-8 py-4">
        <div class="flex justify-between items-center">
          <!-- Info Usuario (izquierda) - Clickeable -->
          <div class="flex items-center gap-4 cursor-pointer group"
               @click="$emit('toggleSettings')">
            <!-- Avatar -->
            <div class="w-10 h-10 rounded-full bg-purple-900/50 border border-purple-500/30
                        group-hover:border-pink-500/50 group-hover:bg-purple-900/70
                        flex items-center justify-center overflow-hidden
                        transition-all duration-300">
              <template v-if="userAvatar">
                <img :src="userAvatar" 
                     :alt="userName"
                     class="w-full h-full object-cover">
              </template>
              <svg v-else 
                   class="w-6 h-6 text-purple-300 group-hover:text-pink-300 transition-colors" 
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <!-- Nombre Usuario -->
            <div class="text-white group-hover:text-pink-500 transition-colors">
              <p class="font-game text-sm">
                {{ userName }}
              </p>
              <p class="text-xs text-gray-400 group-hover:text-pink-400/70">
                {{ userEmail }}
              </p>
            </div>
          </div>
  
          <!-- Título (centro) -->
          <div class="flex items-center gap-8">
            <!-- Botón Atrás -->
            <button v-if="showBack" 
                    @click="$inertia.visit(backUrl)"
                    class="mr-4 text-white/80 hover:text-pink-500 transition-colors">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="2" 
                      d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
  
            <h1 class="font-game text-4xl" v-if="title || subtitle">
              <span class="text-white">{{ title }}</span>
              <span class="text-pink-500" v-if="subtitle">/</span>
              <span class="text-purple-500" v-if="subtitle">{{ subtitle }}</span>
            </h1>
          </div>
  
          <!-- Acciones (derecha) -->
          <div class="flex items-center gap-4">
            <slot name="actions"></slot>
          </div>
        </div>
      </div>
    </header>
  </template>
  
  <script setup>
  import { usePage } from '@inertiajs/vue3'
  import { computed } from 'vue'

  const page = usePage()
  
  const user = computed(() => page.props.auth.user)
  const userAvatar = computed(() => user.value?.avatar)
  const userName = computed(() => user.value?.name || 'Guest')
  const userEmail = computed(() => user.value?.email || 'Click para iniciar sesión')

  defineProps({
    title: {
      type: String,
      required: true
    },
    subtitle: {
      type: String,
      required: true
    },
    showBack: {
      type: Boolean,
      default: true
    },
    backUrl: {
      type: String,
      default: () => route('game.home')
    }
  })

  defineEmits(['toggleSettings'])
  </script>

  <style scoped>
  .font-game {
    font-family: 'GameFont', sans-serif;
  }
  </style>
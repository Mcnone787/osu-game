<template>
  <div class="min-h-screen bg-dark bg-cover bg-center bg-no-repeat flex flex-col justify-between">
    <!-- Header -->
    <header class="w-full px-5 py-3 bg-black/50 text-white flex justify-between items-center shadow-lg">
      <div class="username-container font-game">
        <p class="text-xl">Guest</p>
        <p class="text-sm">¡Haz click para iniciar sesión!</p>
      </div>
      <div class="flex gap-3">
        <button v-for="control in controls" :key="control.icon" class="hover:text-pink-500 transition-colors">
          <component :is="control.icon" class="w-6 h-6"/>
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex justify-center items-center p-5">
      <div class="relative flex items-center">
        <button 
          @click="toggleMenu"
          class="osu-button z-10"
        >
          OSU!
        </button>

        <!-- Menú actualizado -->
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 translate-x-4"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 translate-x-0"
          leave-to-class="opacity-0 translate-x-4"
        >
          <div 
            v-show="isMenuOpen"
            class="menu-container"
          >
            <Link 
              href="/game/play" 
              class="menu-item"
            >
              <span class="font-game text-xl font-bold">Play</span>
            </Link>
          </div>
        </Transition>
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
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const isMenuOpen = ref(false)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const controls = [
  { icon: 'ChevronLeftIcon' },
  { icon: 'PauseIcon' },
  { icon: 'PlayIcon' },
  { icon: 'ChevronRightIcon' }
]
</script> 
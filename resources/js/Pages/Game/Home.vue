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
      <div class="relative flex items-center" 
           :class="{ '-translate-x-52 transition-transform duration-300': isMenuOpen }">
        <button 
          @click="handleClick"
          :disabled="isAnimating"
          class="osu-button z-10"
        >
          BEAT!
        </button>

        <!-- Menú actualizado -->
        <div 
          v-show="isMenuOpen"
          class="menu-container-alt"
        >
          <Link 
            v-for="(item, index) in menuItems"
            :key="index"
            :href="item.href" 
            class="menu-item-alt opacity-0"
            :class="{ 
              'opacity-100': itemVisible[index],
              'translate-y-0': itemVisible[index],
              'translate-y-2': !itemVisible[index]
            }"
            :style="{ 
              width: itemWidths[index] + 'px',
              display: itemWidths[index] === 0 ? 'none' : 'flex'
            }"
          >
            <span class="relative z-10" :class="{ 'opacity-0': itemWidths[index] < 150 }">
              {{ item.text }}
            </span>
          </Link>
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
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const isMenuOpen = ref(false)
const isAnimating = ref(false)
const itemVisible = ref([false, false, false, false])
const itemWidths = ref([0, 0, 0, 0])

const controls = [
  { icon: 'ChevronLeftIcon' },
  { icon: 'PauseIcon' },
  { icon: 'PlayIcon' },
  { icon: 'ChevronRightIcon' }
]

const menuItems = [
  { text: 'Play Now', href: '/game/play' },
  { text: 'Rankings', href: '/game/rankings' },
  { text: 'Profile', href: '/game/profile' },
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
</script>

<style scoped>
.menu-container-alt {
  @apply absolute left-[85%] top-1/2 -translate-y-1/2
         flex flex-col gap-6;
}

.menu-item-alt {
  @apply px-12 py-3
         rounded-lg text-center text-white font-game text-2xl
         items-center justify-center
         bg-purple-800/30
         border border-purple-500/40
         transition-all duration-300;
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
</style> 
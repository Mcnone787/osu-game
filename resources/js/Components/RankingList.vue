<template>
  <div class="ranking-panel bg-purple-950/50 rounded-lg overflow-hidden">
    <div class="flex items-center justify-between p-4 bg-purple-900/50 cursor-pointer"
         @click="isOpen = !isOpen">
      <div class="flex items-center gap-3">
        <component :is="icon" class="w-5 h-5 text-pink-400" />
        <h3 class="font-game text-lg text-white">{{ title }}</h3>
      </div>
      <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
           :class="{ 'rotate-180': isOpen }"
           fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
    
    <div v-show="isOpen" class="ranking-content">
      <div class="p-4 space-y-2">
        <div v-for="rank in rankings" :key="rank.position" 
             class="ranking-item group">
          <div class="flex items-center gap-4">
            <span class="rank-number border-2 rounded-full w-8 h-8 
                       flex items-center justify-center text-lg font-bold font-game"
                  :class="getRankClass(rank.position)">
              #{{ rank.position }}
            </span>
            <div class="relative">
              <img :src="rank.avatar" :alt="rank.player" 
                   class="w-10 h-10 rounded-full border-2 border-purple-500">
              <div class="absolute -bottom-1 -right-1 bg-purple-500 text-xs px-1.5 py-px rounded-full text-[10px]">
                Lv.{{ rank.level }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-game truncate">{{ rank.player }}</h3>
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-300 font-mono">{{ rank.score.toLocaleString() }}</span>
                <span class="text-pink-400 text-xs">x{{ rank.combo }}</span>
              </div>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold text-gradient">{{ rank.accuracy }}%</p>
              <p class="text-xs text-gray-400">{{ rank.date }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: String,
  icon: {
    type: Object,
    required: true
  },
  rankings: {
    type: Array,
    required: true
  }
})

const isOpen = ref(true)

const getRankClass = (position) => {
  if (position === 1) return 'text-yellow-400 border-yellow-400'
  if (position === 2) return 'text-gray-400 border-gray-400'
  if (position === 3) return 'text-amber-600 border-amber-600'
  return 'text-white border-purple-500'
}
</script>

<style scoped>
.ranking-item {
  @apply bg-purple-900/20 rounded-lg p-3
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
</style> 
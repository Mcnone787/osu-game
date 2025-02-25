<template>
  <div class="relative w-fit" ref="dropdownRef">
    <button 
      @click="isOpen = !isOpen"
      class="flex items-center justify-between w-64 px-4 py-3 
             bg-gradient-to-r from-black/60 to-black/50
             rounded-lg border border-purple-500/30
             text-white font-game transition-all duration-300
             hover:border-purple-500/50 hover:from-purple-900/40 hover:to-black/60
             backdrop-blur-md">
      <div class="flex items-center gap-2">
        <component :is="modelValue.icon" class="w-5 h-5 text-purple-400" />
        <span>{{ modelValue.name }}</span>
      </div>
      <ChevronDownIcon 
        class="w-5 h-5 text-purple-400 transition-transform duration-300"
        :class="{ 'rotate-180': isOpen }" />
    </button>

    <div v-if="isOpen" 
         class="absolute top-full left-0 mt-2 w-64 
                bg-gradient-to-b from-black/80 to-black/70
                rounded-lg border border-purple-500/30
                shadow-lg shadow-purple-500/10 backdrop-blur-md
                overflow-hidden z-50">
      <div v-for="option in options" 
           :key="option.id"
           @click="selectOption(option)"
           class="flex items-center gap-2 px-4 py-3 cursor-pointer
                  transition-all duration-300 
                  hover:bg-gradient-to-r hover:from-purple-900/40 hover:to-purple-800/30
                  first:rounded-t-lg last:rounded-b-lg
                  border-b border-purple-500/20 last:border-none">
        <component :is="option.icon" class="w-5 h-5 text-purple-400" />
        <span class="text-gray-200 hover:text-white transition-colors duration-300">
          {{ option.name }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'
import { markRaw } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  options: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])
const isOpen = ref(false)
const dropdownRef = ref(null)

const selectOption = (option) => {
  emit('update:modelValue', option)
  isOpen.value = false
}

const closeDropdown = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})

const ChevronIcon = markRaw(ChevronDownIcon)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> 
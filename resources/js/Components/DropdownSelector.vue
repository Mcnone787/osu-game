<template>
  <div class="relative w-fit" ref="dropdownRef">
    <button 
      @click="isOpen = !isOpen"
      class="flex items-center justify-between w-64 px-4 py-2 
             bg-purple-900/50 rounded-lg border border-purple-500/30
             text-white font-game transition-all duration-300
             hover:bg-purple-900/70">
      <div class="flex items-center gap-2">
        <component :is="modelValue.icon" class="w-5 h-5" />
        <span>{{ modelValue.name }}</span>
      </div>
      <ChevronDownIcon 
        class="w-5 h-5 transition-transform duration-300"
        :class="{ 'rotate-180': isOpen }" />
    </button>

    <div v-if="isOpen" 
         class="absolute top-full left-0 mt-2 w-64 
                bg-purple-900/90 rounded-lg border border-purple-500/30
                shadow-lg z-10">
      <div v-for="option in options" 
           :key="option.id"
           @click="selectOption(option)"
           class="flex items-center gap-2 px-4 py-3 cursor-pointer
                  transition-all duration-300 hover:bg-purple-800/50
                  first:rounded-t-lg last:rounded-b-lg">
        <component :is="option.icon" class="w-5 h-5" />
        <span>{{ option.name }}</span>
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
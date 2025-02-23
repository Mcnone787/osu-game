<template>
  <div v-if="links.length > 3" class="flex flex-wrap -mb-1">
    <template v-for="(link, key) in links" :key="key">
      <div
        v-if="link.url === null"
        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
        v-html="link.label"
      />
      <Link
        v-else
        class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-purple-900/30 focus:border-purple-500 focus:text-purple-500"
        :class="{ 'bg-purple-900/20': link.active }"
        :href="link.url"
        v-html="link.label"
      />
    </template>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  links: {
    type: Array,
    required: true,
  },
})
</script>

<style scoped>
.border {
  @apply border-purple-500/40;
}

.text-gray-400 {
  @apply bg-purple-900/10;
}

a {
  @apply text-white;
}
</style> 
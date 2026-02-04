<script setup lang="ts">
import { Moodboard } from '@/types/generated'

defineProps<{
  moodboards: Moodboard[]
}>()
</script>

<template>
  <div class="p-8">
    <h1 class="text-3xl font-bold mb-6">Your Moodboards</h1>
    
    <div v-if="moodboards.length > 0" class="space-y-6">
      <div v-for="moodboard in moodboards" :key="moodboard.id" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">{{ moodboard.feeling }}</h2>
        
        <div v-if="moodboard.generation_context" class="mt-4">
          <h3 class="font-medium mb-2">Generated Content:</h3>
          <pre class="bg-gray-100 dark:bg-gray-900 p-4 rounded overflow-auto">{{ JSON.stringify(JSON.parse(moodboard.generation_context), null, 2) }}</pre>
        </div>
        
        <div v-else class="text-gray-500">
          <p>Content is being generated... Check back soon!</p>
        </div>
      </div>
    </div>
    
    <div v-else class="text-gray-500">
      <p>No moodboards found. Generate one from the Feelings page!</p>
    </div>
  </div>
</template>

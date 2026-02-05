<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { MoodboardType } from '@/types/moodboard';
import { useMoodboardStore } from '@/stores/moodboardStore';
import { JobStatus } from '@/types/jobStatus';
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
  moodboard: MoodboardType;
  artworks: Array<{
    id: number;
    title: string;
    artist: string;
    imageUrl: string | null;
    source: string | null;
    style: string | null;
    metadata: Record<string, any>;
  }>;
  musicTracks: Array<{
    id: number;
    title: string;
    artist: string;
  }>;
  books: Array<{
    id: number;
    title: string;
    author: string;
  }>;
}>();      

const store = useMoodboardStore();
const jobStatus = computed(() => store.jobStatus);

// Polling logic
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const startPolling = () => {
  if (jobStatus.value === JobStatus.Processing) {
    pollingInterval = setInterval(async () => {
      await store.checkStatus(props.moodboard.id);
    }, 2000);
  }
};

const stopPolling = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

watch(() => jobStatus.value, (newStatus: JobStatus) => {
    if (newStatus === JobStatus.Completed) {
        router.reload();
        stopPolling();
    }
  }
);

onMounted(() => {
    store.setSelectedMoodboard(props.moodboard);
    store.setJobStatus(props.moodboard.jobStatus as JobStatus);
    startPolling();
});

onUnmounted(() => {
  stopPolling();
});
</script>

<template>
  <Head :title="`Moodboard - ${props.moodboard.feeling}`" />

  <AppLayout>
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-white">{{ props.moodboard.feeling }} Moodboard</h1>
          <p class="text-sm text-slate-400">
            Created {{ new Date(props.moodboard.createdAt).toLocaleDateString() }}
          </p>
        </div>
      </div>

      <!-- Paintings Section -->
      <div v-if="props.artworks.length > 0" class="mb-8">
        <h2 class="mb-4 text-2xl font-semibold text-white">Paintings</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="artwork in props.artworks"
            :key="artwork.id"
            class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900"
          >
            <div v-if="artwork.imageUrl" class="aspect-square overflow-hidden bg-slate-950">
              <img
                :src="artwork.imageUrl"
                :alt="artwork.title"
                class="h-full w-full object-cover"
              />
            </div>
            <div v-else class="flex aspect-square items-center justify-center bg-slate-950">
              <span class="text-slate-600">No image available</span>
            </div>
            <div class="p-4">
              <h3 class="text-lg font-semibold text-white">{{ artwork.title }}</h3>
              <p class="text-sm text-slate-400">{{ artwork.artist }}</p>
              <p v-if="artwork.source" class="mt-2 text-xs text-slate-500">{{ artwork.source }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Music Section -->
      <div v-if="props.musicTracks.length > 0" class="mb-8">
        <h2 class="mb-4 text-2xl font-semibold text-white">Music</h2>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
          <div class="space-y-3">
            <div
              v-for="track in props.musicTracks"
              :key="track.id"
              class="flex items-center justify-between border-b border-slate-800 pb-3 last:border-0 last:pb-0"
            >
              <div>
                <p class="font-medium text-white">{{ track.title }}</p>
                <p class="text-sm text-slate-400">{{ track.artist }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Books Section -->
      <div v-if="props.books.length > 0" class="mb-8">
        <h2 class="mb-4 text-2xl font-semibold text-white">Book</h2>
        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
          <div class="space-y-3">
            <div
              v-for="book in props.books"
              :key="book.id"
              class="flex items-center justify-between border-b border-slate-800 pb-3 last:border-0 last:pb-0"
            >
              <div>
                <p class="font-medium text-white">{{ book.title }}</p>
                <p class="text-sm text-slate-400">{{ book.author }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="props.artworks.length === 0 && props.musicTracks.length === 0 && props.books.length === 0"
        class="rounded-xl border border-slate-800 bg-slate-900 p-12 text-center"
      >
        <p class="text-slate-400">
          {{ jobStatus === JobStatus.Processing ? 'Generating content...' : 'No content available yet.' }}
        </p>
      </div>
    </div>
  </AppLayout>
</template>

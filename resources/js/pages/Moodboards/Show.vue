<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { MoodboardType } from '@/types/moodboard';
import { useMoodboardStore } from '@/stores/moodboardStore';
import { JobStatus } from '@/types/jobStatus';
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
  moodboard: MoodboardType;
}>();           

const store = useMoodboardStore();
const jobStatus = computed(() => store.jobStatus);

// Polling logic
let pollingInterval: ReturnType<typeof setInterval> | null = null;

const startPolling = () => {
  if (jobStatus.value === JobStatus.Processing) {
    console.log('Starting polling for moodboard status...');
    pollingInterval = setInterval(async () => {
      await store.checkStatus(props.moodboard.id);
    }, 5000);
  }
};

const stopPolling = () => {
  if (pollingInterval) {
    console.log('Stopping polling for moodboard status...');
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
};

watch(() => jobStatus.value, (newStatus: JobStatus) => {
    if (newStatus === JobStatus.Completed) {
        router.reload({ only: ['moodboard'] });
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

      <div class="rounded-xl border border-slate-800 p-6">
        {{ props.moodboard }}
      </div>
    </div>
  </AppLayout>
</template>

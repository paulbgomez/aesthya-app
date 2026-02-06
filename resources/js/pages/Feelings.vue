<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import FeelingBubble from '@/components/FeelingBubble.vue';
import { EnergyAxis, FeelingType, PleasantnessAxis } from '@/types/feeling';
import { usePastelColors } from '@/composables/useColors';
import { useFeelingStore } from '@/stores/feelingStore';
import { moodboardService } from '@/services/moodboardService';

const props = defineProps<{
    feelings: FeelingType[];
}>();

const emit = defineEmits<{
    (e: 'select', feeling: FeelingType): void;
}>();

const store = useFeelingStore();
const { setSelectedFeeling, clearSelection } = store;
const selectedFeeling = computed(() => store.selectedFeeling);
const isGenerating = ref(false);

const bucketedFeelings = computed(() => {
    const buckets: Record<string, FeelingType[]> = {
        'high-pleasant': [],
        'high-unpleasant': [],
        'low-pleasant': [],
        'low-unpleasant': [],
    };

    props.feelings.forEach((feeling) => {
        const energyKey = (feeling as any).energy_axis ?? feeling.energyAxis; // backend sends snake_case
        const pleasantKey = (feeling as any).pleasantness_axis ?? feeling.pleasantnessAxis;
        const energy = energyKey === EnergyAxis.High ? 'high' : 'low';
        const pleasant = pleasantKey === PleasantnessAxis.Pleasant ? 'pleasant' : 'unpleasant';
        buckets[`${energy}-${pleasant}`].push(feeling);
    });

    const toPlotted = (list: FeelingType[]) =>
        list.map((feeling) => {
            const { pastelColor } = usePastelColors(feeling.color);
            return {
                ...feeling,
                fillColor: pastelColor,
            };
        });

    return {
        highPleasant: toPlotted(buckets['high-pleasant']),
        highUnpleasant: toPlotted(buckets['high-unpleasant']),
        lowPleasant: toPlotted(buckets['low-pleasant']),
        lowUnpleasant: toPlotted(buckets['low-unpleasant']),
    };
});

const feelingsWithClipPaths = computed(() => {
    const mapBucket = (list: any[]) =>
        list.map((feeling) => {
            const isSelected = selectedFeeling.value?.id === feeling.id;
            const clipPath = isSelected ? feeling.finalShape : 'circle(50% at 50% 50%)';
            return {
                ...feeling,
                clipPath,
                isSelected,
            };
        });

    return {
        highPleasant: mapBucket(bucketedFeelings.value.highPleasant),
        highUnpleasant: mapBucket(bucketedFeelings.value.highUnpleasant),
        lowPleasant: mapBucket(bucketedFeelings.value.lowPleasant),
        lowUnpleasant: mapBucket(bucketedFeelings.value.lowUnpleasant),
    };
});

const handleSelect = (feeling: FeelingType) => {
    if (selectedFeeling.value?.id === feeling.id) {
        clearSelection();
    } else {
        setSelectedFeeling(feeling as FeelingType);
        emit('select', feeling);
    }
};

const handleCurateMoodboard = () => {
    if (!selectedFeeling.value?.id) return;

    isGenerating.value = true;
    moodboardService.generateMoodboard(selectedFeeling.value.id);
}
;</script>

<template>
    <Head title="Feelings" />

    <AppLayout>
        <section class="flex h-full flex-1 flex-col gap-6 p-6">
            <header class="flex items-center justify-between gap-2">
                <div>
                    <p class="text-sm uppercase tracking-wide text-slate-400">
                        Feelings Map
                    </p>
                    <h1 class="text-2xl font-semibold text-slate-50">
                        Mood Constellation
                    </h1>
                    <p class="text-sm text-slate-400">
                        Click to select and reveal each feeling's story.
                    </p>
                </div>
            </header>
            <div
                class="relative min-h-[70vh] overflow-hidden rounded-2xl border border-slate-800 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.12),transparent_30%),radial-gradient(circle_at_80%_30%,rgba(244,114,182,0.12),transparent_30%),linear-gradient(135deg,rgba(15,23,42,0.95),rgba(15,23,42,0.9))] p-4"
            >
                <div class="grid h-full w-full grid-cols-2 grid-rows-2 gap-6">

                    <div class="rounded-lg border border-slate-800/60 p-3">
                        <p class="mb-3 text-xs uppercase tracking-wide text-slate-400">
                            High Energy · Unpleasant
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <FeelingBubble
                                v-for="feeling in feelingsWithClipPaths.highUnpleasant"
                                :key="feeling.id"
                                :feeling="feeling"
                                :is-selected="feeling.isSelected"
                                @select="handleSelect"
                            />
                        </div>
                    </div>

                     <div class="rounded-lg border border-slate-800/60 p-3">
                        <p class="mb-3 text-xs uppercase tracking-wide text-slate-400">
                            High Energy · Pleasant
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <FeelingBubble
                                v-for="feeling in feelingsWithClipPaths.highPleasant"
                                :key="feeling.id"
                                :feeling="feeling"
                                :is-selected="feeling.isSelected"
                                @select="handleSelect"
                            />
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-800/60 p-3">
                        <p class="mb-3 text-xs uppercase tracking-wide text-slate-400">
                            Low Energy · Unpleasant
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <FeelingBubble
                                v-for="feeling in feelingsWithClipPaths.lowUnpleasant"
                                :key="feeling.id"
                                :feeling="feeling"
                                :is-selected="feeling.isSelected"
                                @select="handleSelect"
                            />
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-800/60 p-3">
                        <p class="mb-3 text-xs uppercase tracking-wide text-slate-400">
                            Low Energy · Pleasant
                        </p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <FeelingBubble
                                v-for="feeling in feelingsWithClipPaths.lowPleasant"
                                :key="feeling.id"
                                :feeling="feeling"
                                :is-selected="feeling.isSelected"
                                @select="handleSelect"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="selectedFeeling"
                class="rounded-xl border border-slate-800 bg-slate-900 p-6"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="mb-2 text-lg font-semibold text-white">
                            {{ selectedFeeling.name }}
                        </h3>
                        <p class="text-base text-slate-300">
                            {{ selectedFeeling.description || 'No description available.' }}
                        </p>
                    </div>
                    <button
                        @click="handleCurateMoodboard"
                        :disabled="isGenerating"
                        class="shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-all hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isGenerating ? 'Generating...' : 'Curate Moodboard' }}
                    </button>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
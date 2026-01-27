<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import { FeelingType } from '@/types';

const props = defineProps<{
    feelings: FeelingType[];
}>();

const emit = defineEmits<{
    (e: 'select', feeling: FeelingType): void;
}>();

const hashToUnit = (value: string | number) => {
    let h = 2166136261;
    const input = typeof value === 'number' ? value.toString() : value;
    for (let i = 0; i < input.length; i += 1) {
        h ^= input.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    return ((h >>> 0) % 1000) / 1000;
};

const clipShapes = [
    'polygon(20% 0%, 80% 0%, 100% 50%, 80% 100%, 20% 100%, 0% 50%)', // hexagon
    'polygon(50% 0%, 100% 25%, 80% 100%, 20% 100%, 0% 25%)', // pentagon
    'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)', // house-like
    'polygon(0% 20%, 50% 0%, 100% 20%, 80% 100%, 20% 100%)', // kite
    'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)', // octagon
];

const palette = [
    '#0ea5e9',
    '#22c55e',
];

const selectedFeeling = ref<FeelingType | null>(null);

const plottedFeelings = computed(() => {
    const total = Math.max(props.feelings.length, 1);
    return props.feelings.map((feeling, index) => {
        // Vogel spiral for even distribution.
        const theta = index * 137.508; // golden angle degrees
        const r = Math.sqrt((index + 0.5) / total);
        const x = 50 + r * 42 * Math.cos((theta * Math.PI) / 180) + hashToUnit(`${feeling.id}-x`) * 4;
        const y = 50 + r * 42 * Math.sin((theta * Math.PI) / 180) + hashToUnit(`${feeling.id}-y`) * 4;
        const shapeIndex = index % clipShapes.length;
        const colorIndex = index % palette.length;

        return {
            ...feeling,
            x,
            y,
            finalShape: clipShapes[shapeIndex],
            color: palette[colorIndex],
        };
    });
});

const feelingsWithClipPaths = computed(() => {
    const selectedId = selectedFeeling.value?.id;
    return plottedFeelings.value.map((feeling) => ({
        ...feeling,
        clipPath: selectedId === feeling.id ? feeling.finalShape : 'circle(50% at 50% 50%)',
    }));
});

const handleSelect = (feeling: FeelingType) => {
    selectedFeeling.value = feeling;
    emit('select', feeling);
};
</script>

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
                <div class="relative h-full w-full">
                    <div
                        v-for="feeling in feelingsWithClipPaths"
                        :key="feeling.id"
                        class="absolute flex h-20 w-20 -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center text-center transition-transform duration-300"
                        :style="{
                            left: `${feeling.x}%`,
                            top: `${feeling.y}%`,
                        }"
                        @click="handleSelect(feeling)"
                    >
                        <div
                            class="flex h-full w-full items-center justify-center px-2 text-xs font-semibold transition-all duration-500"
                            :style="{
                                clipPath: feeling.clipPath,
                                backgroundColor: feeling.color,
                                color: '#000000',
                            }"
                        >
                            {{ feeling.name }}
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="selectedFeeling"
                class="rounded-xl border border-slate-800 bg-slate-900 p-6"
            >
                <p class="text-base text-white">
                    {{ selectedFeeling.description || 'No description available.' }}
                </p>
            </div>
        </section>
    </AppLayout>
</template>
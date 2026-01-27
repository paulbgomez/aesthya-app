<script setup lang="ts">
import type { FeelingType } from '@/types/feeling';

interface Props {
    feeling: FeelingType & {
        id: number;
        finalShape: string;
        fillColor: string;
        clipPath: string;
    };
    isSelected: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'select', feeling: Props['feeling']): void;
}>();

const handleClick = () => {
    emit('select', props.feeling);
};
</script>

<template>
    <div
        class="flex h-20 w-20 cursor-pointer items-center justify-center text-center transition-transform duration-300"
        @click="handleClick"
    >
        <div
            class="flex h-full w-full items-center justify-center px-2 text-xs font-semibold transition-all duration-500"
            :style="{
                clipPath: feeling.clipPath,
                backgroundColor: feeling.fillColor,
                color: '#000000',
            }"
        >
            {{ feeling.name }}
        </div>
    </div>
</template>

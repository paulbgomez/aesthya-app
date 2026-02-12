<script lang="ts" setup>
import { useDraggable } from '@vueuse/core';
import { Ref, ref, useTemplateRef } from 'vue';

defineProps<{ 
    title: string,
    subtitle: string,
    imageUrl: string,
    description: string,
}>();

const cardElement: Ref<HTMLElement | null> = useTemplateRef('card');
const isFlipped = ref(false);

const flipCard = (): void => {
    isFlipped.value = !isFlipped.value;
};

useDraggable(cardElement);
</script>

<template>
    <button class="card w-full max-w-[320px] shadow-xs" ref="card" type="button" @click="flipCard">
        <div class="card__inner relative h-[420px] w-full" :class="{ flipped: isFlipped }">
            <div class="card__front absolute inset-0 flex flex-col gap-4 rounded-lg border border-neutral-300 bg-white">
                <div class="flex-1 overflow-hidden rounded-lg">
                    <img class="h-full w-full object-cover inset-shadow-sm" referrerpolicy="no-referrer" :src="imageUrl" :alt="title" />
                </div>
            </div>
            <div class="card__back absolute inset-0 flex flex-col items-center justify-center rounded-lg border border-neutral-300 bg-white p-4">
                <h2 class="">{{ title }}</h2>
                <h3 class="">{{ subtitle }}</h3>
                <p class="">{{ description }}</p>
            </div>
        </div>
    </button>
</template>

<style scoped lang="scss">
    .card {
        perspective: 1200px;
        border: none;
        background: transparent;
        padding: 0;
    }
    
    .card__inner {
        -webkit-transform-style: preserve-3d;
        transform-style: preserve-3d;
        transition: -webkit-transform 0.6s;
        -webkit-transition: -webkit-transform 0.6s;
        transition: transform 0.6s;
        transition: transform 0.6s, -webkit-transform 0.6s;
        cursor: pointer;
    }

    .card__inner.flipped {
        -webkit-transform: rotateY(180deg);
        transform: rotateY(180deg);
    }

    .card__front,
    .card__back {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .card__back {
        -webkit-transform: rotateY(180deg);
        transform: rotateY(180deg);
    }
</style>
<script setup lang="ts">
import { computed, ref } from 'vue';
import type { ProductImage } from '@/types';

const props = defineProps<{
    images: ProductImage[];
    name: string;
}>();

const activeIndex = ref(0);

const activeImage = computed(
    () => props.images[activeIndex.value] ?? props.images[0],
);
</script>

<template>
    <div class="space-y-3">
        <div
            class="relative aspect-[4/5] overflow-hidden rounded-2xl bg-secondary"
        >
            <img
                v-if="activeImage"
                :src="activeImage.url"
                :alt="activeImage.alt ?? name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-accent via-secondary to-muted"
            >
                <span class="font-serif text-3xl text-primary/50">{{
                    name
                }}</span>
            </div>
        </div>

        <div v-if="images.length > 1" class="grid grid-cols-4 gap-2">
            <button
                v-for="(image, index) in images"
                :key="image.id"
                type="button"
                :class="[
                    'aspect-square overflow-hidden rounded-lg border-2 bg-secondary transition-colors',
                    index === activeIndex
                        ? 'border-primary'
                        : 'border-transparent hover:border-border',
                ]"
                @click="activeIndex = index"
            >
                <img
                    :src="image.url"
                    :alt="image.alt ?? name"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
            </button>
        </div>
    </div>
</template>

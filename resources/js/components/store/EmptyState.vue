<script setup lang="ts">
import { ShoppingBag } from '@lucide/vue';

defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card/60 px-6 py-16 text-center"
    >
        <div
            class="mb-4 flex size-12 items-center justify-center rounded-full bg-accent text-primary"
        >
            <slot name="icon">
                <ShoppingBag class="size-5" />
            </slot>
        </div>
        <h3 class="font-serif text-2xl font-medium">{{ title }}</h3>
        <p
            v-if="description"
            class="mt-2 max-w-md text-sm text-muted-foreground"
        >
            {{ description }}
        </p>
        <div v-if="$slots.default" class="mt-6">
            <slot />
        </div>
    </div>
</template>

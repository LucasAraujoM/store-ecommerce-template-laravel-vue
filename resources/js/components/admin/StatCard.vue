<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    label: string;
    value: string;
    hint?: string;
    icon: Component;
}>();
</script>

<template>
    <div class="admin-card p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p
                    class="text-[11px] font-medium tracking-wider text-muted-foreground uppercase"
                >
                    {{ label }}
                </p>
                <p class="mt-2 font-serif text-3xl leading-none font-medium">
                    {{ value }}
                </p>
                <p v-if="hint" class="mt-2 text-xs text-muted-foreground">
                    {{ hint }}
                </p>
            </div>
            <div
                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-accent text-primary"
            >
                <component :is="icon" class="size-4" />
            </div>
        </div>
    </div>
</template>

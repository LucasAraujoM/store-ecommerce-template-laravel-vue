<script setup lang="ts">
import { Minus, Plus } from '@lucide/vue';

const model = defineModel<number>({ default: 1 });

const props = withDefaults(
    defineProps<{
        min?: number;
        max?: number;
        compact?: boolean;
    }>(),
    {
        min: 1,
        max: 10,
        compact: false,
    },
);

function decrease(): void {
    model.value = Math.max(props.min, model.value - 1);
}

function increase(): void {
    model.value = Math.min(props.max, model.value + 1);
}
</script>

<template>
    <div
        :class="[
            'inline-flex items-center rounded-lg border border-input bg-card',
            compact ? 'h-9' : 'h-11',
        ]"
    >
        <button
            type="button"
            class="flex h-full items-center justify-center px-3 text-muted-foreground transition-colors hover:text-foreground disabled:opacity-30"
            :disabled="model <= min"
            aria-label="Restar cantidad"
            @click="decrease"
        >
            <Minus class="size-3.5" />
        </button>
        <span
            :class="[
                'min-w-8 text-center text-sm font-medium tabular-nums',
                compact ? 'text-xs' : '',
            ]"
        >
            {{ model }}
        </span>
        <button
            type="button"
            class="flex h-full items-center justify-center px-3 text-muted-foreground transition-colors hover:text-foreground disabled:opacity-30"
            :disabled="model >= max"
            aria-label="Sumar cantidad"
            @click="increase"
        >
            <Plus class="size-3.5" />
        </button>
    </div>
</template>

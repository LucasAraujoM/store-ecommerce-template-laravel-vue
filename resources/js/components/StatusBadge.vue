<script setup lang="ts">
import { computed } from 'vue';
import { statusClasses } from '@/lib/format';

const props = defineProps<{
    status: string;
    label?: string;
}>();

const classes = computed(() => statusClasses(props.status));
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide uppercase',
            classes,
        ]"
    >
        {{ label ?? status }}
    </span>
</template>

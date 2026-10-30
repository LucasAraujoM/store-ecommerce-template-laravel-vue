<script setup lang="ts">
import { nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types';

const props = defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    scrollTo?: string;
}>();

const visibleLinks = () => props.links;

function scrollToTarget(): void {
    const target = props.scrollTo;

    if (!target) {
        return;
    }

    nextTick(() => {
        document
            .getElementById(target)
            ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
</script>

<template>
    <div
        v-if="total > 0"
        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-xs text-muted-foreground">
            Mostrando {{ from ?? 0 }}–{{ to ?? 0 }} de {{ total }}
        </p>

        <nav class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in visibleLinks()" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    @success="scrollToTarget"
                    :class="[
                        'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-xs font-medium transition-colors',
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground',
                    ]"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-transparent px-2 text-xs text-muted-foreground/40"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>

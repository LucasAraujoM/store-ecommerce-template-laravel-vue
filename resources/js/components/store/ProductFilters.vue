<script setup lang="ts">
import { ref } from 'vue';
import { X } from '@lucide/vue';
import type { CategorySummary } from '@/types';

type ColorOption = {
    color: string;
    color_hex: string | null;
};

const props = defineProps<{
    categories: CategorySummary[];
    sizes: string[];
    colors: ColorOption[];
    filters: {
        q: string;
        category: string;
        size: string;
        color: string;
        min: number | null;
        max: number | null;
    };
}>();

const emit = defineEmits<{
    apply: [filters: Record<string, unknown>];
    clear: [];
}>();

const search = ref(props.filters.q);
const min = ref<number | null>(props.filters.min);
const max = ref<number | null>(props.filters.max);

function submitSearch(): void {
    emit('apply', { q: search.value.trim() });
}

function toggle(key: 'category' | 'size' | 'color', value: string): void {
    emit('apply', { [key]: props.filters[key] === value ? '' : value });
}

function togglePrice(): void {
    emit('apply', { min: min.value, max: max.value });
}
</script>

<template>
    <div class="space-y-8">
        <div>
            <p class="eyebrow">Buscar</p>
            <form class="relative mt-3" @submit.prevent="submitSearch">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Nombre o palabra clave"
                    class="field-input pr-9"
                />
                <button
                    v-if="search"
                    type="button"
                    class="absolute top-0 right-2 flex h-11 items-center px-2 text-muted-foreground hover:text-foreground"
                    aria-label="Limpiar búsqueda"
                    @click="
                        search = '';
                        emit('apply', { q: '' });
                    "
                >
                    <X class="size-4" />
                </button>
            </form>
        </div>

        <div v-if="categories.length">
            <p class="eyebrow">Categorías</p>
            <ul class="mt-3 space-y-1">
                <li>
                    <button
                        type="button"
                        :class="[
                            'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors',
                            filters.category === ''
                                ? 'bg-accent font-medium text-accent-foreground'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground',
                        ]"
                        @click="toggle('category', '')"
                    >
                        Todas las prendas
                    </button>
                </li>
                <li v-for="category in categories" :key="category.id">
                    <button
                        type="button"
                        :class="[
                            'flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm transition-colors',
                            filters.category === category.slug
                                ? 'bg-accent font-medium text-accent-foreground'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground',
                        ]"
                        @click="toggle('category', category.slug)"
                    >
                        <span>{{ category.name }}</span>
                        <span class="text-[10px] tabular-nums opacity-60">
                            {{ category.products_count ?? '' }}
                        </span>
                    </button>
                </li>
            </ul>
        </div>

        <div v-if="sizes.length">
            <p class="eyebrow">Talle</p>
            <div class="mt-3 flex flex-wrap gap-1.5">
                <button
                    v-for="size in sizes"
                    :key="size"
                    type="button"
                    :class="[
                        'inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-2 text-xs font-medium transition-colors',
                        filters.size === size
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground',
                    ]"
                    @click="toggle('size', size)"
                >
                    {{ size }}
                </button>
            </div>
        </div>

        <div v-if="colors.length">
            <p class="eyebrow">Color</p>
            <ul class="mt-3 space-y-1">
                <li v-for="option in colors" :key="option.color">
                    <button
                        type="button"
                        :class="[
                            'flex w-full items-center gap-2.5 rounded-md px-2 py-1.5 text-left text-sm transition-colors',
                            filters.color === option.color
                                ? 'bg-accent font-medium text-accent-foreground'
                                : 'text-muted-foreground hover:bg-secondary hover:text-foreground',
                        ]"
                        @click="toggle('color', option.color)"
                    >
                        <span
                            class="size-4 rounded-full border border-border"
                            :style="{
                                backgroundColor: option.color_hex ?? '#d6cfc6',
                            }"
                        />
                        {{ option.color }}
                    </button>
                </li>
            </ul>
        </div>

        <div>
            <p class="eyebrow">Precio</p>
            <form class="mt-3" @submit.prevent="togglePrice">
                <div class="flex items-center gap-2">
                    <input
                        v-model.number="min"
                        type="number"
                        min="0"
                        step="1000"
                        placeholder="Mín."
                        class="field-input field-input-sm"
                    />
                    <span class="text-muted-foreground">—</span>
                    <input
                        v-model.number="max"
                        type="number"
                        min="0"
                        step="1000"
                        placeholder="Máx."
                        class="field-input field-input-sm"
                    />
                </div>
                <button
                    type="submit"
                    class="mt-2 w-full rounded-md border border-border bg-card py-2 text-xs font-medium transition-colors hover:border-primary/40 hover:text-primary"
                >
                    Aplicar precio
                </button>
            </form>
        </div>

        <button
            type="button"
            class="w-full rounded-md border border-dashed border-border py-2.5 text-xs font-medium text-muted-foreground transition-colors hover:border-primary/40 hover:text-primary"
            @click="emit('clear')"
        >
            Limpiar filtros
        </button>
    </div>
</template>

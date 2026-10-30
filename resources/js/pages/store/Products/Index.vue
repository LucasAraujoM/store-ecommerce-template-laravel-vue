<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ChevronDown, SlidersHorizontal, X } from '@lucide/vue';
import Pagination from '@/components/Pagination.vue';
import ProductCard from '@/components/store/ProductCard.vue';
import ProductFilters from '@/components/store/ProductFilters.vue';
import { index as productsIndex } from '@/routes/products';
import type { CategorySummary, Paginated, Product } from '@/types';

const props = defineProps<{
    products: Paginated<Product>;
    categories: CategorySummary[];
    sizes: string[];
    colors: Array<{ color: string; color_hex: string | null }>;
    filters: {
        q: string;
        category: string;
        size: string;
        color: string;
        sort: string;
        min: number | null;
        max: number | null;
    };
}>();

const mobileFiltersOpen = ref(false);
const sort = ref(props.filters.sort || 'recent');

const hasFilters = computed(
    () =>
        props.filters.q !== '' ||
        props.filters.category !== '' ||
        props.filters.size !== '' ||
        props.filters.color !== '' ||
        props.filters.min !== null ||
        props.filters.max !== null,
);

const activeCategory = computed(() =>
    props.categories.find(
        (category) => category.slug === props.filters.category,
    ),
);

function cleanParams(
    overrides: Record<string, unknown> = {},
): Record<string, string> {
    const merged = { ...props.filters, ...overrides };
    const params: Record<string, string> = {};

    Object.entries(merged).forEach(([key, value]) => {
        if (
            value === null ||
            value === undefined ||
            value === '' ||
            value === 'recent'
        ) {
            return;
        }

        params[key] = String(value);
    });

    return params;
}

function apply(overrides: Record<string, unknown> = {}): void {
    mobileFiltersOpen.value = false;

    router.get(productsIndex().url, cleanParams(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function clearFilters(): void {
    mobileFiltersOpen.value = false;
    sort.value = 'recent';
    router.get(
        productsIndex().url,
        {},
        { preserveScroll: true, replace: true },
    );
}

function changeSort(): void {
    apply({ sort: sort.value });
}

const filterProps = computed(() => ({
    categories: props.categories,
    sizes: props.sizes,
    colors: props.colors,
    filters: props.filters,
}));
</script>

<template>
    <Head :title="activeCategory ? activeCategory.name : 'Tienda'" />

    <div class="border-b border-border/70 bg-secondary/30">
        <div class="store-container py-10 sm:py-14">
            <p class="eyebrow">Lunaria</p>
            <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                {{ activeCategory ? activeCategory.name : 'Toda la tienda' }}
            </h1>
            <p class="mt-3 max-w-xl text-sm text-muted-foreground">
                {{
                    activeCategory?.description ??
                    'Prendas femeninas de producción local, pensadas para durar y sentirse bien.'
                }}
            </p>
        </div>
    </div>

    <div class="store-container py-8 lg:py-12">
        <div class="grid gap-10 lg:grid-cols-[250px_1fr]">
            <aside class="hidden lg:block">
                <ProductFilters
                    v-bind="filterProps"
                    @apply="apply"
                    @clear="clearFilters"
                />
            </aside>

            <div>
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-border/70 pb-4"
                >
                    <p class="text-xs text-muted-foreground">
                        {{ products.total }}
                        {{ products.total === 1 ? 'prenda' : 'prendas' }}
                    </p>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-9 items-center gap-2 rounded-md border border-border bg-card px-3 text-xs font-medium lg:hidden"
                            @click="mobileFiltersOpen = true"
                        >
                            <SlidersHorizontal class="size-3.5" />
                            Filtros
                            <span
                                v-if="hasFilters"
                                class="flex size-4 items-center justify-center rounded-full bg-primary text-[9px] text-primary-foreground"
                            >
                                !
                            </span>
                        </button>

                        <div class="relative">
                            <select
                                v-model="sort"
                                class="h-9 appearance-none rounded-md border border-border bg-card pr-8 pl-3 text-xs font-medium outline-none focus:border-ring"
                                @change="changeSort"
                            >
                                <option value="recent">Más recientes</option>
                                <option value="price_asc">Menor precio</option>
                                <option value="price_desc">Mayor precio</option>
                                <option value="name">Nombre A–Z</option>
                            </select>
                            <ChevronDown
                                class="pointer-events-none absolute top-1/2 right-2.5 size-3.5 -translate-y-1/2 text-muted-foreground"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="hasFilters"
                    class="mt-4 flex flex-wrap items-center gap-2"
                >
                    <span class="text-[11px] text-muted-foreground"
                        >Filtros activos:</span
                    >
                    <button
                        v-if="filters.category"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-1 text-[11px] font-medium text-accent-foreground"
                        @click="apply({ category: '' })"
                    >
                        {{ activeCategory?.name ?? filters.category }}
                        <X class="size-3" />
                    </button>
                    <button
                        v-if="filters.size"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-1 text-[11px] font-medium text-accent-foreground"
                        @click="apply({ size: '' })"
                    >
                        Talle {{ filters.size }}
                        <X class="size-3" />
                    </button>
                    <button
                        v-if="filters.color"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-1 text-[11px] font-medium text-accent-foreground"
                        @click="apply({ color: '' })"
                    >
                        {{ filters.color }}
                        <X class="size-3" />
                    </button>
                    <button
                        v-if="filters.q"
                        type="button"
                        class="inline-flex items-center gap-1 rounded-full bg-accent px-2.5 py-1 text-[11px] font-medium text-accent-foreground"
                        @click="apply({ q: '' })"
                    >
                        “{{ filters.q }}”
                        <X class="size-3" />
                    </button>
                </div>

                <div
                    v-if="products.data.length"
                    id="productos"
                    class="mt-6 grid scroll-mt-24 grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 lg:scroll-mt-28 xl:grid-cols-3"
                >
                    <ProductCard
                        v-for="(product, index) in products.data"
                        :key="product.id"
                        :product="product"
                        :priority="index < 3"
                    />
                </div>

                <div
                    v-else
                    class="mt-10 rounded-2xl border border-dashed border-border bg-card/50 px-6 py-16 text-center"
                >
                    <h2 class="font-serif text-2xl">No encontramos prendas</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Probá con otros filtros o mirá toda la colección.
                    </p>
                    <button
                        type="button"
                        class="mt-5 rounded-full bg-primary px-6 py-2.5 text-[11px] font-semibold tracking-[0.14em] text-primary-foreground uppercase"
                        @click="clearFilters"
                    >
                        Ver todo
                    </button>
                </div>

                <div class="mt-10">
                    <Pagination
                        :links="products.links"
                        :from="products.from"
                        :to="products.to"
                        :total="products.total"
                        scroll-to="productos"
                    />
                </div>
            </div>
        </div>
    </div>

    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-200"
        leave-to-class="opacity-0"
    >
        <div v-if="mobileFiltersOpen" class="fixed inset-0 z-50 lg:hidden">
            <div
                class="absolute inset-0 bg-foreground/30 backdrop-blur-sm"
                @click="mobileFiltersOpen = false"
            />
            <div
                class="absolute inset-y-0 right-0 flex w-[88%] max-w-sm flex-col bg-background shadow-2xl"
            >
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-4"
                >
                    <p class="font-serif text-xl">Filtros</p>
                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-full hover:bg-secondary"
                        aria-label="Cerrar filtros"
                        @click="mobileFiltersOpen = false"
                    >
                        <X class="size-5" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-6">
                    <ProductFilters
                        v-bind="filterProps"
                        @apply="apply"
                        @clear="clearFilters"
                    />
                </div>
            </div>
        </div>
    </Transition>
</template>

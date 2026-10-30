<script setup lang="ts">
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Search, Star, Trash2 } from '@lucide/vue';
import Pagination from '@/components/Pagination.vue';
import { formatMoney } from '@/lib/format';
import {
    create as adminProductCreate,
    destroy as adminProductDestroy,
    edit as adminProductEdit,
    index as adminProductsIndex,
} from '@/routes/admin/products';
import type { CategorySummary, Paginated, Product } from '@/types';

const props = defineProps<{
    products: Paginated<Product>;
    categories: CategorySummary[];
    filters: {
        q: string;
        category: string;
        status: string;
    };
}>();

const filters = reactive({ ...props.filters });

function apply(): void {
    const params: Record<string, string> = {};

    if (filters.q.trim() !== '') {
        params.q = filters.q.trim();
    }

    if (filters.category !== '') {
        params.category = filters.category;
    }

    if (filters.status !== '') {
        params.status = filters.status;
    }

    router.get(adminProductsIndex().url, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function reset(): void {
    filters.q = '';
    filters.category = '';
    filters.status = '';
    router.get(
        adminProductsIndex().url,
        {},
        { preserveScroll: true, replace: true },
    );
}

function destroy(product: Product): void {
    if (
        !window.confirm(
            `¿Eliminar “${product.name}”? Esta acción no se puede deshacer.`,
        )
    ) {
        return;
    }

    router.delete(adminProductDestroy(product.id).url, {
        preserveScroll: true,
    });
}

function stockOf(product: Product): number {
    if (typeof product.variants_sum_stock === 'number') {
        return product.variants_sum_stock;
    }

    return (product.variants ?? []).reduce(
        (total, variant) => total + variant.stock,
        0,
    );
}
</script>

<template>
    <Head title="Productos" />

    <div class="admin-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Catálogo</p>
                <h1 class="mt-1 font-serif text-3xl font-medium sm:text-4xl">
                    Productos
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ products.total }} productos en el catálogo.
                </p>
            </div>
            <Link
                :href="adminProductCreate()"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                <Plus class="size-4" />
                Nuevo producto
            </Link>
        </div>

        <form
            class="admin-card mt-6 flex flex-wrap items-end gap-3 p-4"
            @submit.prevent="apply"
        >
            <div class="min-w-[200px] flex-1">
                <label class="field-label" for="q">Buscar</label>
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        id="q"
                        v-model="filters.q"
                        type="search"
                        placeholder="Nombre o slug"
                        class="field-input field-input-sm pl-9"
                    />
                </div>
            </div>
            <div class="w-full sm:w-44">
                <label class="field-label" for="category">Categoría</label>
                <select
                    id="category"
                    v-model="filters.category"
                    class="field-input field-input-sm"
                >
                    <option value="">Todas</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.slug"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>
            <div class="w-full sm:w-36">
                <label class="field-label" for="status">Estado</label>
                <select
                    id="status"
                    v-model="filters.status"
                    class="field-input field-input-sm"
                >
                    <option value="">Todos</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                </select>
            </div>
            <button
                type="submit"
                class="h-9 rounded-md bg-primary px-4 text-xs font-medium text-primary-foreground hover:bg-primary/90"
            >
                Filtrar
            </button>
            <button
                type="button"
                class="h-9 rounded-md border border-border px-3 text-xs text-muted-foreground hover:text-foreground"
                @click="reset"
            >
                Limpiar
            </button>
        </form>

        <div class="admin-card mt-5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-border text-[10px] tracking-wider text-muted-foreground uppercase"
                        >
                            <th class="px-4 py-3 font-medium">Producto</th>
                            <th class="px-4 py-3 font-medium">Categoría</th>
                            <th class="px-4 py-3 font-medium">Precio</th>
                            <th class="px-4 py-3 font-medium">Stock</th>
                            <th class="px-4 py-3 font-medium">Variantes</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="border-b border-border/60 last:border-b-0 hover:bg-secondary/30"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-12 shrink-0 overflow-hidden rounded-md bg-secondary"
                                    >
                                        <img
                                            v-if="product.primary_image_url"
                                            :src="product.primary_image_url"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="min-w-0">
                                        <Link
                                            :href="adminProductEdit(product.id)"
                                            class="flex items-center gap-1.5 font-medium transition-colors hover:text-primary"
                                        >
                                            {{ product.name }}
                                            <Star
                                                v-if="product.is_featured"
                                                class="size-3 fill-amber-400 text-amber-400"
                                            />
                                        </Link>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            {{ product.slug }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ product.category?.name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ formatMoney(product.price_cents) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[11px] font-semibold',
                                        stockOf(product) === 0
                                            ? 'bg-red-500/10 text-red-700 dark:text-red-300'
                                            : stockOf(product) <= 5
                                              ? 'bg-amber-500/10 text-amber-700 dark:text-amber-300'
                                              : 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                    ]"
                                >
                                    {{ stockOf(product) }} u.
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ product.variants?.length ?? 0 }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase',
                                        product.is_active
                                            ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                            : 'bg-secondary text-muted-foreground',
                                    ]"
                                >
                                    {{
                                        product.is_active
                                            ? 'Activo'
                                            : 'Inactivo'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Link
                                        :href="adminProductEdit(product.id)"
                                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                                        aria-label="Editar"
                                    >
                                        <Pencil class="size-3.5" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        aria-label="Eliminar"
                                        @click="destroy(product)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.data.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                No hay productos con esos filtros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            <Pagination
                :links="products.links"
                :from="products.from"
                :to="products.to"
                :total="products.total"
            />
        </div>
    </div>
</template>

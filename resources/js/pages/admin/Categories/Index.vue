<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Tags, Trash2 } from '@lucide/vue';
import {
    create as adminCategoryCreate,
    destroy as adminCategoryDestroy,
    edit as adminCategoryEdit,
} from '@/routes/admin/categories';
import type { CategorySummary } from '@/types';

defineProps<{
    categories: CategorySummary[];
}>();

function destroy(category: CategorySummary): void {
    if (
        !window.confirm(
            `¿Eliminar la categoría “${category.name}”? Los productos quedarán sin categoría.`,
        )
    ) {
        return;
    }

    router.delete(adminCategoryDestroy(category.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Categorías" />

    <div class="admin-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Catálogo</p>
                <h1 class="mt-1 font-serif text-3xl font-medium sm:text-4xl">
                    Categorías
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Organizá la tienda y definí el orden del menú.
                </p>
            </div>
            <Link
                :href="adminCategoryCreate()"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                <Plus class="size-4" />
                Nueva categoría
            </Link>
        </div>

        <div
            v-if="categories.length"
            class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3"
        >
            <article
                v-for="category in categories"
                :key="category.id"
                class="admin-card flex gap-4 p-4"
            >
                <div
                    class="size-20 shrink-0 overflow-hidden rounded-lg bg-secondary"
                >
                    <img
                        v-if="category.image_url"
                        :src="category.image_url"
                        :alt="category.name"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full items-center justify-center text-muted-foreground/40"
                    >
                        <Tags class="size-6" />
                    </div>
                </div>

                <div class="flex min-w-0 flex-1 flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h2
                                    class="truncate font-serif text-xl font-medium"
                                >
                                    {{ category.name }}
                                </h2>
                                <p
                                    class="truncate text-[10px] text-muted-foreground"
                                >
                                    /{{ category.slug }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'shrink-0 rounded-full px-2 py-0.5 text-[9px] font-semibold uppercase',
                                    category.is_active !== false
                                        ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                        : 'bg-secondary text-muted-foreground',
                                ]"
                            >
                                {{
                                    category.is_active !== false
                                        ? 'Activa'
                                        : 'Inactiva'
                                }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-[11px] text-muted-foreground">
                            {{ category.products_count ?? 0 }}
                            {{
                                (category.products_count ?? 0) === 1
                                    ? 'producto'
                                    : 'productos'
                            }}
                            · Orden {{ category.sort_order ?? 0 }}
                        </p>
                    </div>

                    <div class="mt-3 flex items-center gap-1.5">
                        <Link
                            :href="adminCategoryEdit(category.id)"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-border px-2.5 text-[11px] font-medium transition-colors hover:border-primary/40 hover:text-primary"
                        >
                            <Pencil class="size-3" />
                            Editar
                        </Link>
                        <button
                            type="button"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md px-2.5 text-[11px] text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                            @click="destroy(category)"
                        >
                            <Trash2 class="size-3" />
                            Eliminar
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div
            v-else
            class="mt-6 rounded-2xl border border-dashed border-border bg-card/60 px-6 py-16 text-center"
        >
            <Tags class="mx-auto size-8 text-muted-foreground/40" />
            <h2 class="mt-3 font-serif text-2xl">Todavía no hay categorías</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Creá la primera para organizar tus prendas.
            </p>
            <Link
                :href="adminCategoryCreate()"
                class="mt-5 inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-5 text-xs font-semibold text-primary-foreground"
            >
                <Plus class="size-4" />
                Nueva categoría
            </Link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ImagePlus, Save, X } from '@lucide/vue';
import {
    index as adminCategoriesIndex,
    store as adminCategoryStore,
    update as adminCategoryUpdate,
} from '@/routes/admin/categories';
import type { CategorySummary } from '@/types';

const props = defineProps<{
    category: CategorySummary | null;
}>();

const form = useForm({
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    description: props.category?.description ?? '',
    image: null as File | null,
    is_active: props.category?.is_active ?? true,
    sort_order: props.category?.sort_order ?? 0,
});

const isEditing = computed(() => props.category !== null);
const preview = ref<string | null>(null);

let lastAutoSlug = props.category?.slug ?? '';

function slugify(value: string): string {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

watch(
    () => form.name,
    (name) => {
        const generated = slugify(name);

        if (form.slug === '' || form.slug === lastAutoSlug) {
            form.slug = generated;
            lastAutoSlug = generated;
        }
    },
);

function onFile(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }

    form.image = file;
    preview.value = file ? URL.createObjectURL(file) : null;
    input.value = '';
}

function clearNewImage(): void {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }

    preview.value = null;
    form.image = null;
}

function submit(): void {
    const options = { preserveScroll: true, forceFormData: true };

    if (props.category) {
        form.put(adminCategoryUpdate(props.category.id).url, options);

        return;
    }

    form.post(adminCategoryStore().url, options);
}
</script>

<template>
    <Head :title="isEditing ? `Editar ${category?.name}` : 'Nueva categoría'" />

    <div class="admin-container">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link
                    :href="adminCategoriesIndex()"
                    class="flex size-9 items-center justify-center rounded-full border border-border bg-card transition-colors hover:border-primary/40"
                    aria-label="Volver"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <p class="eyebrow">Catálogo</p>
                    <h1
                        class="mt-0.5 font-serif text-2xl font-medium sm:text-3xl"
                    >
                        {{ isEditing ? 'Editar categoría' : 'Nueva categoría' }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="adminCategoriesIndex()"
                    class="inline-flex h-10 items-center rounded-lg border border-border bg-card px-4 text-xs font-medium transition-colors hover:border-primary/40"
                >
                    Cancelar
                </Link>
                <button
                    type="button"
                    :disabled="form.processing"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50"
                    @click="submit"
                >
                    <Save class="size-4" />
                    {{ form.processing ? 'Guardando…' : 'Guardar' }}
                </button>
            </div>
        </div>

        <form
            class="mt-6 grid gap-5 lg:grid-cols-[1fr_320px] lg:items-start"
            @submit.prevent="submit"
        >
            <section class="admin-card p-5">
                <h2 class="font-serif text-xl font-medium">Información</h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <label class="field-label" for="name">Nombre</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="field-input"
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="field-label" for="slug">Slug (URL)</label>
                        <input
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            class="field-input font-mono text-xs"
                        />
                        <p
                            v-if="form.errors.slug"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.slug }}
                        </p>
                    </div>
                    <div>
                        <label class="field-label" for="description"
                            >Descripción</label
                        >
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            maxlength="2000"
                            class="field-input h-auto py-2.5"
                            placeholder="Aparece en el encabezado del catálogo."
                        />
                        <p
                            v-if="form.errors.description"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                </div>
            </section>

            <aside class="space-y-4">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Publicación</h2>
                    <div class="mt-4 space-y-3">
                        <label
                            class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5"
                        >
                            <span>
                                <span class="block text-xs font-medium"
                                    >Activa</span
                                >
                                <span
                                    class="block text-[10px] text-muted-foreground"
                                    >Visible en la tienda</span
                                >
                            </span>
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="size-4 accent-[var(--primary)]"
                            />
                        </label>
                        <div>
                            <label class="field-label" for="sort_order"
                                >Orden</label
                            >
                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="field-input"
                            />
                            <p class="mt-1 text-[10px] text-muted-foreground">
                                Menor número aparece primero.
                            </p>
                            <p
                                v-if="form.errors.sort_order"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.sort_order }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Imagen</h2>
                    <div class="mt-4">
                        <div
                            v-if="preview || category?.image_url"
                            class="relative aspect-[4/3] overflow-hidden rounded-lg bg-secondary"
                        >
                            <img
                                :src="preview ?? category?.image_url ?? ''"
                                :alt="form.name || 'Categoría'"
                                class="h-full w-full object-cover"
                            />
                            <button
                                v-if="preview"
                                type="button"
                                class="absolute top-1.5 right-1.5 flex size-7 items-center justify-center rounded-full bg-black/60 text-white"
                                aria-label="Quitar imagen"
                                @click="clearNewImage"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>
                        <label
                            class="mt-3 flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-border bg-secondary/30 px-4 py-6 text-center transition-colors hover:border-primary/40"
                        >
                            <ImagePlus class="size-5 text-muted-foreground" />
                            <span class="text-xs font-medium">
                                {{
                                    category?.image_url
                                        ? 'Reemplazar imagen'
                                        : 'Subir imagen'
                                }}
                            </span>
                            <span class="text-[10px] text-muted-foreground"
                                >JPG, PNG o WebP · 4 MB</span
                            >
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="onFile"
                            />
                        </label>
                        <p
                            v-if="form.errors.image"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.image }}
                        </p>
                    </div>
                </section>
            </aside>
        </form>
    </div>
</template>

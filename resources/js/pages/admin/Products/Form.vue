<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ImagePlus, Plus, Save, Star, Trash2, X } from '@lucide/vue';
import {
    index as adminProductsIndex,
    store as adminProductStore,
    update as adminProductUpdate,
} from '@/routes/admin/products';
import type { CategorySummary, Product } from '@/types';

type VariantInput = {
    id: number | null;
    size: string;
    color: string;
    color_hex: string;
    sku: string;
    stock: number;
    price: number | string;
    is_active: boolean;
};

const props = defineProps<{
    product: Product | null;
    categories: CategorySummary[];
}>();

const defaultVariant = (): VariantInput => ({
    id: null,
    size: 'S',
    color: 'Negro',
    color_hex: '#1c1a19',
    sku: '',
    stock: 0,
    price: '',
    is_active: true,
});

const form = useForm({
    name: props.product?.name ?? '',
    slug: props.product?.slug ?? '',
    category_id: props.product?.category_id ?? null,
    short_description: props.product?.short_description ?? '',
    description: props.product?.description ?? '',
    price: props.product ? props.product.price_cents / 100 : '',
    compare_at_price: props.product?.compare_at_price_cents
        ? props.product.compare_at_price_cents / 100
        : '',
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
    images: [] as File[],
    remove_images: [] as number[],
    variants: (props.product?.variants ?? []).map<VariantInput>((variant) => ({
        id: variant.id,
        size: variant.size,
        color: variant.color,
        color_hex: variant.color_hex ?? '#1c1a19',
        sku: variant.sku ?? '',
        stock: variant.stock,
        price: variant.price_cents !== null ? variant.price_cents / 100 : '',
        is_active: variant.is_active,
    })),
});

if (form.variants.length === 0) {
    form.variants = [defaultVariant()];
}

const newImages = ref<Array<{ file: File; url: string }>>([]);
const isEditing = computed(() => props.product !== null);

let lastAutoSlug = props.product?.slug ?? '';

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

function onFiles(event: Event): void {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);

    files.forEach((file) => {
        form.images.push(file);
        newImages.value.push({ file, url: URL.createObjectURL(file) });
    });

    input.value = '';
}

function removeNewImage(index: number): void {
    URL.revokeObjectURL(newImages.value[index].url);
    newImages.value.splice(index, 1);
    form.images.splice(index, 1);
}

function toggleRemoveImage(id: number): void {
    if (form.remove_images.includes(id)) {
        form.remove_images = form.remove_images.filter(
            (imageId) => imageId !== id,
        );
    } else {
        form.remove_images.push(id);
    }
}

function addVariant(): void {
    form.variants.push(defaultVariant());
}

function removeVariant(index: number): void {
    if (form.variants.length <= 1) {
        return;
    }

    form.variants.splice(index, 1);
}

function error(path: string): string | undefined {
    return (form.errors as Record<string, string>)[path];
}

function submit(): void {
    const options = {
        preserveScroll: true,
        forceFormData: true,
    };

    if (props.product) {
        form.put(adminProductUpdate(props.product.id).url, options);
    } else {
        form.post(adminProductStore().url, options);
    }
}
</script>

<template>
    <Head :title="isEditing ? `Editar ${product?.name}` : 'Nuevo producto'" />

    <div class="admin-container">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link
                    :href="adminProductsIndex()"
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
                        {{ isEditing ? 'Editar producto' : 'Nuevo producto' }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="adminProductsIndex()"
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
            class="mt-6 grid gap-5 lg:grid-cols-[1fr_340px] lg:items-start"
            @submit.prevent="submit"
        >
            <div class="space-y-5">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Información</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="name">Nombre</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="field-input"
                            />
                            <p
                                v-if="error('name')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('name') }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="slug"
                                >Slug (URL)</label
                            >
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="field-input font-mono text-xs"
                            />
                            <p
                                v-if="error('slug')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('slug') }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="category"
                                >Categoría</label
                            >
                            <select
                                id="category"
                                v-model="form.category_id"
                                class="field-input"
                            >
                                <option :value="null">Sin categoría</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <p
                                v-if="error('category_id')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('category_id') }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="short_description"
                                >Descripción corta</label
                            >
                            <textarea
                                id="short_description"
                                v-model="form.short_description"
                                rows="2"
                                class="field-input h-auto py-2.5"
                                maxlength="500"
                            />
                            <p
                                v-if="error('short_description')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('short_description') }}
                            </p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="description"
                                >Descripción completa</label
                            >
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="6"
                                class="field-input h-auto py-2.5"
                            />
                            <p
                                v-if="error('description')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('description') }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <div
                        class="flex flex-wrap items-center justify-between gap-3"
                    >
                        <div>
                            <h2 class="font-serif text-xl font-medium">
                                Variantes y stock
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Cada combinación de talle y color tiene su
                                propio stock.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-border px-3 text-xs font-medium transition-colors hover:border-primary/40 hover:text-primary"
                            @click="addVariant"
                        >
                            <Plus class="size-3.5" />
                            Agregar variante
                        </button>
                    </div>

                    <div
                        class="mt-4 hidden gap-2 px-2 text-[10px] tracking-wider text-muted-foreground uppercase md:grid md:grid-cols-[70px_1fr_54px_1fr_70px_90px_56px_32px]"
                    >
                        <span>Talle</span>
                        <span>Color</span>
                        <span>Hex</span>
                        <span>SKU</span>
                        <span>Stock</span>
                        <span>Precio</span>
                        <span>Activa</span>
                        <span />
                    </div>

                    <div class="mt-2 space-y-2">
                        <div
                            v-for="(variant, index) in form.variants"
                            :key="variant.id ?? `new-${index}`"
                            class="grid gap-2 rounded-lg border border-border/70 bg-secondary/30 p-3 md:grid-cols-[70px_1fr_54px_1fr_70px_90px_56px_32px] md:items-center md:bg-transparent md:p-2"
                        >
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >Talle</label
                                >
                                <input
                                    v-model="variant.size"
                                    type="text"
                                    class="field-input field-input-sm"
                                    placeholder="S"
                                />
                                <p
                                    v-if="error(`variants.${index}.size`)"
                                    class="mt-1 text-[10px] text-destructive"
                                >
                                    {{ error(`variants.${index}.size`) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >Color</label
                                >
                                <input
                                    v-model="variant.color"
                                    type="text"
                                    class="field-input field-input-sm"
                                    placeholder="Negro"
                                />
                                <p
                                    v-if="error(`variants.${index}.color`)"
                                    class="mt-1 text-[10px] text-destructive"
                                >
                                    {{ error(`variants.${index}.color`) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >Hex</label
                                >
                                <input
                                    v-model="variant.color_hex"
                                    type="color"
                                    class="h-9 w-full cursor-pointer rounded-md border border-input bg-card p-1"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >SKU</label
                                >
                                <input
                                    v-model="variant.sku"
                                    type="text"
                                    class="field-input field-input-sm font-mono text-[11px]"
                                    placeholder="Opcional"
                                />
                                <p
                                    v-if="error(`variants.${index}.sku`)"
                                    class="mt-1 text-[10px] text-destructive"
                                >
                                    {{ error(`variants.${index}.sku`) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >Stock</label
                                >
                                <input
                                    v-model.number="variant.stock"
                                    type="number"
                                    min="0"
                                    class="field-input field-input-sm"
                                />
                                <p
                                    v-if="error(`variants.${index}.stock`)"
                                    class="mt-1 text-[10px] text-destructive"
                                >
                                    {{ error(`variants.${index}.stock`) }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="mb-1 block text-[10px] text-muted-foreground uppercase md:hidden"
                                    >Precio</label
                                >
                                <input
                                    v-model="variant.price"
                                    type="number"
                                    min="0"
                                    step="100"
                                    class="field-input field-input-sm"
                                    placeholder="Base"
                                />
                            </div>
                            <label
                                class="flex items-center gap-1.5 text-[10px] text-muted-foreground md:justify-center"
                            >
                                <input
                                    v-model="variant.is_active"
                                    type="checkbox"
                                    class="accent-[var(--primary)]"
                                />
                                <span class="md:hidden">Activa</span>
                            </label>
                            <button
                                type="button"
                                class="flex size-8 items-center justify-center justify-self-end rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive disabled:opacity-30"
                                :disabled="form.variants.length <= 1"
                                aria-label="Quitar variante"
                                @click="removeVariant(index)"
                            >
                                <Trash2 class="size-3.5" />
                            </button>
                        </div>
                    </div>
                    <p
                        v-if="error('variants')"
                        class="mt-2 text-xs text-destructive"
                    >
                        {{ error('variants') }}
                    </p>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Imágenes</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        La primera imagen se usa como portada. JPG, PNG o WebP
                        hasta 4 MB.
                    </p>

                    <div
                        v-if="product?.images?.length"
                        class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4"
                    >
                        <button
                            v-for="image in product.images"
                            :key="image.id"
                            type="button"
                            :class="[
                                'group relative aspect-square overflow-hidden rounded-lg border-2 bg-secondary transition-opacity',
                                form.remove_images.includes(image.id)
                                    ? 'border-destructive opacity-40'
                                    : 'border-transparent',
                            ]"
                            @click="toggleRemoveImage(image.id)"
                        >
                            <img
                                :src="image.url"
                                :alt="image.alt ?? product.name"
                                class="h-full w-full object-cover"
                            />
                            <span
                                :class="[
                                    'absolute inset-0 flex items-center justify-center bg-black/40 text-white transition-opacity',
                                    form.remove_images.includes(image.id)
                                        ? 'opacity-100'
                                        : 'opacity-0 group-hover:opacity-100',
                                ]"
                            >
                                <Trash2
                                    v-if="
                                        !form.remove_images.includes(image.id)
                                    "
                                    class="size-4"
                                />
                                <span
                                    v-else
                                    class="text-[10px] font-semibold uppercase"
                                    >Se quitará</span
                                >
                            </span>
                        </button>
                    </div>

                    <div
                        v-if="newImages.length"
                        class="mt-3 grid grid-cols-3 gap-2 sm:grid-cols-4"
                    >
                        <div
                            v-for="(image, index) in newImages"
                            :key="image.url"
                            class="relative aspect-square overflow-hidden rounded-lg bg-secondary"
                        >
                            <img
                                :src="image.url"
                                :alt="image.file.name"
                                class="h-full w-full object-cover"
                            />
                            <button
                                type="button"
                                class="absolute top-1 right-1 flex size-6 items-center justify-center rounded-full bg-black/60 text-white"
                                aria-label="Quitar imagen"
                                @click="removeNewImage(index)"
                            >
                                <X class="size-3" />
                            </button>
                        </div>
                    </div>

                    <label
                        class="mt-4 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-border bg-secondary/30 px-4 py-7 text-center transition-colors hover:border-primary/40"
                    >
                        <ImagePlus class="size-5 text-muted-foreground" />
                        <span class="text-xs font-medium">Subir imágenes</span>
                        <span class="text-[10px] text-muted-foreground"
                            >Podés seleccionar varias</span
                        >
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="hidden"
                            @change="onFiles"
                        />
                    </label>
                    <p
                        v-if="error('images.0')"
                        class="mt-2 text-xs text-destructive"
                    >
                        {{ error('images.0') }}
                    </p>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-6">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Publicación</h2>
                    <div class="mt-4 space-y-3">
                        <label
                            class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5"
                        >
                            <span>
                                <span class="block text-xs font-medium"
                                    >Activo</span
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
                        <label
                            class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5"
                        >
                            <span>
                                <span
                                    class="flex items-center gap-1 text-xs font-medium"
                                >
                                    Destacado
                                    <Star
                                        class="size-3 fill-amber-400 text-amber-400"
                                    />
                                </span>
                                <span
                                    class="block text-[10px] text-muted-foreground"
                                    >Aparece en la home</span
                                >
                            </span>
                            <input
                                v-model="form.is_featured"
                                type="checkbox"
                                class="size-4 accent-[var(--primary)]"
                            />
                        </label>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Precio</h2>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="field-label" for="price"
                                >Precio (ARS)</label
                            >
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="100"
                                class="field-input"
                                placeholder="58900"
                            />
                            <p
                                v-if="error('price')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('price') }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="compare"
                                >Precio anterior
                                <span class="normal-case opacity-60"
                                    >(opcional)</span
                                ></label
                            >
                            <input
                                id="compare"
                                v-model="form.compare_at_price"
                                type="number"
                                min="0"
                                step="100"
                                class="field-input"
                                placeholder="69900"
                            />
                            <p
                                v-if="error('compare_at_price')"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ error('compare_at_price') }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-4 rounded-lg bg-secondary/60 p-3 text-[11px] leading-relaxed text-muted-foreground"
                    >
                        El precio de cada variante es opcional. Si queda vacío,
                        se usa el precio base del producto.
                    </div>
                </section>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-primary text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-50"
                >
                    <Save class="size-4" />
                    {{
                        form.processing
                            ? 'Guardando…'
                            : isEditing
                              ? 'Guardar cambios'
                              : 'Crear producto'
                    }}
                </button>

                <p
                    v-if="Object.keys(form.errors).length > 0"
                    class="text-center text-[11px] text-destructive"
                >
                    Revisá los campos marcados antes de guardar.
                </p>
            </aside>
        </form>
    </div>
</template>

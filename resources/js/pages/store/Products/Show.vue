<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronRight,
    RefreshCcw,
    Ruler,
    ShieldCheck,
    Truck,
} from '@lucide/vue';
import ProductCard from '@/components/store/ProductCard.vue';
import ProductGallery from '@/components/store/ProductGallery.vue';
import QuantityStepper from '@/components/store/QuantityStepper.vue';
import { discountPercent, formatMoney } from '@/lib/format';
import { store as addToCart } from '@/routes/cart';
import { index as productsIndex } from '@/routes/products';
import type { Product, ProductVariant } from '@/types';

const props = defineProps<{
    product: Product;
    related: Product[];
}>();

const variants = computed(() =>
    props.product.variants.filter((variant) => variant.is_active),
);

const colors = computed(() => {
    const map = new Map<
        string,
        { name: string; hex: string | null; inStock: boolean }
    >();

    variants.value.forEach((variant) => {
        const current = map.get(variant.color);

        map.set(variant.color, {
            name: variant.color,
            hex: variant.color_hex ?? current?.hex ?? null,
            inStock: (current?.inStock ?? false) || variant.stock > 0,
        });
    });

    return Array.from(map.values());
});

const initialVariant =
    variants.value.find((variant) => variant.stock > 0) ??
    variants.value[0] ??
    null;

const selectedColor = ref(initialVariant?.color ?? '');
const selectedSize = ref(initialVariant?.size ?? '');
const quantity = ref(1);
const processing = ref(false);

watch(selectedColor, (color) => {
    const available = variants.value.filter(
        (variant) => variant.color === color,
    );
    const keepSize = available.find(
        (variant) => variant.size === selectedSize.value && variant.stock > 0,
    );

    if (!keepSize) {
        selectedSize.value =
            available.find((variant) => variant.stock > 0)?.size ??
            available[0]?.size ??
            '';
    }
});

const sizesForColor = computed(() =>
    variants.value.filter((variant) => variant.color === selectedColor.value),
);

const selectedVariant = computed<ProductVariant | null>(
    () =>
        variants.value.find(
            (variant) =>
                variant.color === selectedColor.value &&
                variant.size === selectedSize.value,
        ) ?? null,
);

const priceCents = computed(
    () =>
        selectedVariant.value?.effective_price_cents ??
        props.product.price_cents,
);

const discount = computed(() =>
    discountPercent(priceCents.value, props.product.compare_at_price_cents),
);

const canAdd = computed(
    () => selectedVariant.value !== null && selectedVariant.value.stock > 0,
);

const maxQuantity = computed(() =>
    Math.max(1, Math.min(10, selectedVariant.value?.stock ?? 1)),
);

watch(selectedVariant, () => {
    quantity.value = 1;
});

function add(): void {
    if (!selectedVariant.value || !canAdd.value) {
        return;
    }

    processing.value = true;

    router.post(
        addToCart().url,
        {
            product_variant_id: selectedVariant.value.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="product.name" />

    <div class="store-container py-6">
        <nav
            class="flex flex-wrap items-center gap-1.5 text-[11px] text-muted-foreground"
        >
            <Link
                :href="productsIndex()"
                class="transition-colors hover:text-foreground"
            >
                Tienda
            </Link>
            <ChevronRight class="size-3" />
            <Link
                v-if="product.category"
                :href="
                    productsIndex({
                        query: { category: product.category.slug },
                    })
                "
                class="transition-colors hover:text-foreground"
            >
                {{ product.category.name }}
            </Link>
            <ChevronRight v-if="product.category" class="size-3" />
            <span class="text-foreground">{{ product.name }}</span>
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:gap-14">
            <ProductGallery :images="product.images" :name="product.name" />

            <div class="lg:sticky lg:top-28 lg:self-start">
                <p class="eyebrow">{{ product.category?.name ?? 'Lunaria' }}</p>
                <h1
                    class="mt-2 font-serif text-3xl leading-tight font-medium sm:text-4xl lg:text-5xl"
                >
                    {{ product.name }}
                </h1>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="text-xl font-semibold">{{
                        formatMoney(priceCents)
                    }}</span>
                    <span
                        v-if="product.compare_at_price_cents"
                        class="text-sm text-muted-foreground line-through"
                    >
                        {{ formatMoney(product.compare_at_price_cents) }}
                    </span>
                    <span
                        v-if="discount"
                        class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-primary uppercase"
                    >
                        {{ discount }}% off
                    </span>
                </div>

                <p class="mt-2 text-[11px] text-muted-foreground">
                    3 y 6 cuotas sin interés con MercadoPago
                </p>

                <p
                    v-if="product.short_description"
                    class="mt-5 text-sm leading-relaxed text-muted-foreground"
                >
                    {{ product.short_description }}
                </p>

                <div class="mt-7 space-y-5 border-t border-border/70 pt-6">
                    <div v-if="colors.length">
                        <div class="flex items-center justify-between">
                            <p
                                class="text-[11px] font-medium tracking-wider uppercase"
                            >
                                Color
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {{ selectedColor }}
                            </p>
                        </div>
                        <div class="mt-2.5 flex flex-wrap gap-2">
                            <button
                                v-for="color in colors"
                                :key="color.name"
                                type="button"
                                :class="[
                                    'relative flex size-9 items-center justify-center rounded-full border-2 transition-all',
                                    selectedColor === color.name
                                        ? 'border-primary'
                                        : 'border-transparent hover:border-border',
                                    !color.inStock ? 'opacity-40' : '',
                                ]"
                                :title="color.name"
                                :aria-label="color.name"
                                @click="selectedColor = color.name"
                            >
                                <span
                                    class="size-6 rounded-full border border-black/10"
                                    :style="{
                                        backgroundColor: color.hex ?? '#d6cfc6',
                                    }"
                                />
                                <span
                                    v-if="!color.inStock"
                                    class="absolute inset-x-1 top-1/2 h-px rotate-45 bg-foreground/60"
                                />
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <p
                                class="text-[11px] font-medium tracking-wider uppercase"
                            >
                                Talle
                            </p>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition-colors hover:text-foreground"
                            >
                                <Ruler class="size-3" />
                                Guía de talles
                            </button>
                        </div>
                        <div class="mt-2.5 flex flex-wrap gap-2">
                            <button
                                v-for="variant in sizesForColor"
                                :key="variant.id"
                                type="button"
                                :disabled="variant.stock <= 0"
                                :class="[
                                    'inline-flex h-10 min-w-12 items-center justify-center rounded-md border px-3 text-xs font-medium transition-colors',
                                    selectedSize === variant.size
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border bg-card hover:border-primary/40',
                                    variant.stock <= 0
                                        ? 'cursor-not-allowed text-muted-foreground/35 line-through'
                                        : '',
                                ]"
                                @click="selectedSize = variant.size"
                            >
                                {{ variant.size }}
                            </button>
                        </div>
                    </div>

                    <div class="flex items-end gap-3">
                        <div>
                            <p class="field-label">Cantidad</p>
                            <QuantityStepper
                                v-model="quantity"
                                :max="maxQuantity"
                            />
                        </div>
                        <button
                            type="button"
                            :disabled="!canAdd || processing"
                            class="h-11 flex-1 rounded-full bg-primary px-6 text-[11px] font-semibold tracking-[0.16em] text-primary-foreground uppercase transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="add"
                        >
                            {{
                                processing
                                    ? 'Agregando…'
                                    : canAdd
                                      ? 'Agregar al carrito'
                                      : 'Sin stock'
                            }}
                        </button>
                    </div>

                    <p
                        v-if="
                            selectedVariant &&
                            selectedVariant.stock > 0 &&
                            selectedVariant.stock <= 3
                        "
                        class="text-xs font-medium text-amber-700 dark:text-amber-300"
                    >
                        ¡Últimas {{ selectedVariant.stock }} unidades!
                    </p>
                    <p
                        v-else-if="selectedVariant"
                        class="text-xs text-muted-foreground"
                    >
                        Disponible · se envía en 24/48 h
                    </p>
                </div>

                <div
                    class="mt-7 grid grid-cols-3 gap-2 border-y border-border/70 py-4"
                >
                    <div class="flex flex-col items-center gap-1.5 text-center">
                        <Truck class="size-4 text-primary" />
                        <span
                            class="text-[10px] leading-tight text-muted-foreground"
                            >Envío a todo el país</span
                        >
                    </div>
                    <div class="flex flex-col items-center gap-1.5 text-center">
                        <RefreshCcw class="size-4 text-primary" />
                        <span
                            class="text-[10px] leading-tight text-muted-foreground"
                            >Cambios en 15 días</span
                        >
                    </div>
                    <div class="flex flex-col items-center gap-1.5 text-center">
                        <ShieldCheck class="size-4 text-primary" />
                        <span
                            class="text-[10px] leading-tight text-muted-foreground"
                            >Pago protegido</span
                        >
                    </div>
                </div>

                <div class="mt-2 divide-y divide-border/70">
                    <details class="group py-4" open>
                        <summary
                            class="flex cursor-pointer items-center justify-between text-sm font-medium"
                        >
                            Descripción
                            <ChevronRight
                                class="size-4 transition-transform group-open:rotate-90"
                            />
                        </summary>
                        <p
                            class="mt-3 text-sm leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{
                                product.description ?? product.short_description
                            }}
                        </p>
                    </details>

                    <details class="group py-4">
                        <summary
                            class="flex cursor-pointer items-center justify-between text-sm font-medium"
                        >
                            Composición y cuidados
                            <ChevronRight
                                class="size-4 transition-transform group-open:rotate-90"
                            />
                        </summary>
                        <ul
                            class="mt-3 space-y-1.5 text-sm text-muted-foreground"
                        >
                            <li>Composición: consultar etiqueta interior.</li>
                            <li>
                                Lavar a mano o en programa delicado con agua
                                fría.
                            </li>
                            <li>
                                No usar secadora. Planchar a baja temperatura.
                            </li>
                        </ul>
                    </details>

                    <details class="group py-4">
                        <summary
                            class="flex cursor-pointer items-center justify-between text-sm font-medium"
                        >
                            Envíos y devoluciones
                            <ChevronRight
                                class="size-4 transition-transform group-open:rotate-90"
                            />
                        </summary>
                        <div
                            class="mt-3 space-y-2 text-sm text-muted-foreground"
                        >
                            <p>
                                Enviamos a todo el país con Correo Argentino y
                                Andreani. También podés retirar en Palermo sin
                                cargo.
                            </p>
                            <p>
                                Tenés 15 días desde que recibís tu pedido para
                                solicitar un cambio.
                            </p>
                        </div>
                    </details>
                </div>
            </div>
        </div>

        <section v-if="related.length" class="mt-20">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Combiná con</p>
                    <h2 class="mt-2 font-serif text-3xl font-medium">
                        También te puede gustar
                    </h2>
                </div>
            </div>
            <div class="mt-7 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">
                <ProductCard
                    v-for="item in related"
                    :key="item.id"
                    :product="item"
                />
            </div>
        </section>
    </div>
</template>

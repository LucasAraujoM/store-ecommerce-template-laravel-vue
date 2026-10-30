<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Trash2, Truck } from '@lucide/vue';
import EmptyState from '@/components/store/EmptyState.vue';
import QuantityStepper from '@/components/store/QuantityStepper.vue';
import { formatMoney } from '@/lib/format';
import {
    clear as cartClear,
    destroy as cartDestroy,
    update as cartUpdate,
} from '@/routes/cart';
import { create as checkoutCreate } from '@/routes/checkout';
import { index as productsIndex, show as productShow } from '@/routes/products';
import type { CartLine, CartSummary } from '@/types';

const props = defineProps<{
    cart: CartSummary;
}>();

const processing = ref<number | null>(null);

const freeShippingReached = computed(
    () => props.cart.subtotal_cents >= props.cart.free_shipping_from_cents,
);

const remainingForFreeShipping = computed(() =>
    Math.max(
        0,
        props.cart.free_shipping_from_cents - props.cart.subtotal_cents,
    ),
);

const progress = computed(() => {
    if (props.cart.free_shipping_from_cents <= 0) {
        return 100;
    }

    return Math.min(
        100,
        Math.round(
            (props.cart.subtotal_cents / props.cart.free_shipping_from_cents) *
                100,
        ),
    );
});

function updateQuantity(line: CartLine, quantity: number): void {
    if (quantity === line.quantity) {
        return;
    }

    processing.value = line.id;

    router.patch(
        cartUpdate(line.id).url,
        { quantity },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = null;
            },
        },
    );
}

function removeLine(line: CartLine): void {
    processing.value = line.id;

    router.delete(cartDestroy(line.id).url, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = null;
        },
    });
}

function clearCart(): void {
    router.delete(cartClear().url, { preserveScroll: true });
}
</script>

<template>
    <Head title="Tu carrito" />

    <div class="store-container py-10 lg:py-14">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Tu selección</p>
                <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                    Carrito
                </h1>
            </div>
            <p v-if="cart.count" class="text-xs text-muted-foreground">
                {{ cart.count }} {{ cart.count === 1 ? 'prenda' : 'prendas' }}
            </p>
        </div>

        <EmptyState
            v-if="cart.items.length === 0"
            title="Tu carrito está vacío"
            description="Explorá la colección y encontrá esa prenda que te está esperando."
            class="mt-8"
        >
            <Link
                :href="productsIndex()"
                class="inline-flex h-11 items-center gap-2 rounded-full bg-primary px-6 text-[11px] font-semibold tracking-[0.16em] text-primary-foreground uppercase transition-colors hover:bg-primary/90"
            >
                Ver la colección
                <ArrowRight class="size-4" />
            </Link>
        </EmptyState>

        <div
            v-else
            class="mt-8 grid gap-10 lg:grid-cols-[1fr_380px] lg:items-start"
        >
            <div>
                <div class="rounded-2xl border border-border bg-card">
                    <div
                        v-for="line in cart.items"
                        :key="line.id"
                        class="flex gap-4 border-b border-border/70 p-4 last:border-b-0 sm:p-5"
                        :class="processing === line.id ? 'opacity-60' : ''"
                    >
                        <Link
                            :href="productShow(line.product_slug)"
                            class="block size-24 shrink-0 overflow-hidden rounded-lg bg-secondary sm:size-28"
                        >
                            <img
                                v-if="line.product_image"
                                :src="line.product_image"
                                :alt="line.product_name"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="flex h-full items-center justify-center text-[10px] text-muted-foreground"
                            >
                                Sin imagen
                            </div>
                        </Link>

                        <div
                            class="flex min-w-0 flex-1 flex-col justify-between gap-3"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <Link
                                        :href="productShow(line.product_slug)"
                                        class="font-serif text-lg leading-tight font-medium transition-colors hover:text-primary"
                                    >
                                        {{ line.product_name }}
                                    </Link>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Talle {{ line.size }} · {{ line.color }}
                                    </p>
                                    <p
                                        v-if="!line.available"
                                        class="mt-1 text-[11px] font-medium text-destructive"
                                    >
                                        Stock insuficiente
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full text-muted-foreground transition-colors hover:bg-secondary hover:text-destructive"
                                    aria-label="Quitar del carrito"
                                    :disabled="processing === line.id"
                                    @click="removeLine(line)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>

                            <div
                                class="flex flex-wrap items-end justify-between gap-3"
                            >
                                <QuantityStepper
                                    :model-value="line.quantity"
                                    :max="Math.min(10, line.stock)"
                                    compact
                                    @update:model-value="
                                        (value) => updateQuantity(line, value)
                                    "
                                />
                                <div class="text-right">
                                    <p class="text-sm font-semibold">
                                        {{ formatMoney(line.total_cents) }}
                                    </p>
                                    <p
                                        v-if="line.quantity > 1"
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ formatMoney(line.unit_price_cents) }}
                                        c/u
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <Link
                        :href="productsIndex()"
                        class="link-underline text-xs tracking-[0.14em] uppercase"
                    >
                        Seguir comprando
                    </Link>
                    <button
                        type="button"
                        class="text-xs text-muted-foreground underline-offset-4 transition-colors hover:text-destructive hover:underline"
                        @click="clearCart"
                    >
                        Vaciar carrito
                    </button>
                </div>
            </div>

            <aside class="lg:sticky lg:top-28">
                <div
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <h2 class="font-serif text-2xl font-medium">Resumen</h2>

                    <div
                        class="mt-5 space-y-3 border-b border-border/70 pb-5 text-sm"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span class="font-medium">{{
                                formatMoney(cart.subtotal_cents)
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Envío</span>
                            <span class="text-xs"
                                >Se calcula en el checkout</span
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-5">
                        <span class="text-sm font-medium">Total estimado</span>
                        <span class="font-serif text-2xl font-semibold">
                            {{ formatMoney(cart.subtotal_cents) }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-secondary/70 p-3.5">
                        <div class="flex items-center gap-2 text-xs">
                            <Truck class="size-4 text-primary" />
                            <span
                                v-if="freeShippingReached"
                                class="font-medium text-primary"
                            >
                                ¡Tenés envío gratis!
                            </span>
                            <span v-else class="text-muted-foreground">
                                Te faltan
                                <strong class="text-foreground">{{
                                    formatMoney(remainingForFreeShipping)
                                }}</strong>
                                para envío gratis
                            </span>
                        </div>
                        <div
                            class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-border"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-all duration-500"
                                :style="{ width: `${progress}%` }"
                            />
                        </div>
                    </div>

                    <Link
                        :href="checkoutCreate()"
                        class="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-[11px] font-semibold tracking-[0.16em] text-primary-foreground uppercase transition-colors hover:bg-primary/90"
                    >
                        Iniciar compra
                        <ArrowRight class="size-4" />
                    </Link>

                    <p
                        class="mt-3 text-center text-[10px] leading-relaxed text-muted-foreground"
                    >
                        Pago seguro con MercadoPago o transferencia bancaria.
                        <br />
                        Podés comprar como invitado.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</template>

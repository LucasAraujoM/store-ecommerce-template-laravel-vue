<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Copy,
    CreditCard,
    Landmark,
    MapPin,
    Package,
    Truck,
} from '@lucide/vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { formatDateTime, formatMoney } from '@/lib/format';
import { index as productsIndex } from '@/routes/products';
import { show as trackShow } from '@/routes/orders/track';
import type { Order } from '@/types';

const props = defineProps<{
    order: Order;
    transfer: {
        bank: string;
        account_name: string;
        cuit: string;
        cbu: string;
        alias: string;
        discount_percent: number;
    };
}>();

const page = usePage();

const timeline = computed(() =>
    [...props.order.status_histories].sort(
        (a, b) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    ),
);

const isTransferPending = computed(
    () =>
        props.order.payment_method === 'transfer' &&
        props.order.payment_status === 'pending',
);

const mailtoLink = computed(
    () =>
        `mailto:${page.props.store.contact.email}?subject=${encodeURIComponent(
            `Comprobante de transferencia ${props.order.number}`,
        )}`,
);

async function copyToClipboard(value: string): Promise<void> {
    await navigator.clipboard.writeText(value);
}
</script>

<template>
    <Head :title="`Pedido ${order.number}`" />

    <div class="store-container py-10 lg:py-14">
        <div class="mx-auto max-w-3xl text-center">
            <div
                class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600"
            >
                <CheckCircle2 class="size-7" />
            </div>
            <p class="eyebrow mt-5">Pedido confirmado</p>
            <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                ¡Gracias por tu compra!
            </h1>
            <p class="mt-3 text-sm text-muted-foreground">
                Recibimos tu pedido
                <strong class="text-foreground">{{ order.number }}</strong
                >. Te enviamos la confirmación a {{ order.customer_email }}.
            </p>
            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <StatusBadge
                    :status="order.status"
                    :label="order.status_label"
                />
                <span class="text-[11px] text-muted-foreground">
                    {{ formatDateTime(order.created_at) }}
                </span>
            </div>
        </div>

        <div
            v-if="isTransferPending"
            class="mx-auto mt-8 max-w-3xl rounded-2xl border border-primary/20 bg-accent/60 p-5 sm:p-6"
        >
            <div class="flex items-start gap-3">
                <Landmark class="mt-0.5 size-5 shrink-0 text-primary" />
                <div class="min-w-0 flex-1">
                    <h2 class="font-serif text-2xl font-medium">
                        Completá tu pago por transferencia
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Transferí el total y enviá el comprobante para que
                        preparemos tu pedido.
                    </p>

                    <div class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                        <div class="rounded-lg bg-card/80 px-3 py-2.5">
                            <p
                                class="text-[10px] tracking-wide text-muted-foreground uppercase"
                            >
                                Banco
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ transfer.bank }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-card/80 px-3 py-2.5">
                            <p
                                class="text-[10px] tracking-wide text-muted-foreground uppercase"
                            >
                                Titular
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ transfer.account_name }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-card/80 px-3 py-2.5">
                            <p
                                class="text-[10px] tracking-wide text-muted-foreground uppercase"
                            >
                                CUIT
                            </p>
                            <p class="mt-0.5 font-medium">
                                {{ transfer.cuit }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-card/80 px-3 py-2.5">
                            <p
                                class="text-[10px] tracking-wide text-muted-foreground uppercase"
                            >
                                Alias
                            </p>
                            <button
                                type="button"
                                class="mt-0.5 flex items-center gap-1.5 font-medium"
                                @click="copyToClipboard(transfer.alias)"
                            >
                                {{ transfer.alias }}
                                <Copy class="size-3 text-muted-foreground" />
                            </button>
                        </div>
                        <div
                            class="rounded-lg bg-card/80 px-3 py-2.5 sm:col-span-2"
                        >
                            <p
                                class="text-[10px] tracking-wide text-muted-foreground uppercase"
                            >
                                CBU
                            </p>
                            <button
                                type="button"
                                class="mt-0.5 flex items-center gap-1.5 font-mono text-sm font-medium"
                                @click="copyToClipboard(transfer.cbu)"
                            >
                                {{ transfer.cbu }}
                                <Copy class="size-3 text-muted-foreground" />
                            </button>
                        </div>
                    </div>

                    <div
                        class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg bg-primary px-4 py-3 text-primary-foreground"
                    >
                        <span class="text-xs tracking-wide uppercase"
                            >Total a transferir</span
                        >
                        <span class="font-serif text-2xl font-semibold">{{
                            formatMoney(order.total_cents)
                        }}</span>
                    </div>

                    <a
                        :href="mailtoLink"
                        class="mt-4 inline-flex h-10 items-center gap-2 rounded-full bg-primary px-5 text-[11px] font-semibold tracking-[0.14em] text-primary-foreground uppercase transition-colors hover:bg-primary/90"
                    >
                        Enviar comprobante
                        <ArrowRight class="size-3.5" />
                    </a>
                    <p class="mt-2 text-[11px] text-muted-foreground">
                        Incluí el número de pedido {{ order.number }} en el
                        asunto o en el mensaje.
                    </p>
                </div>
            </div>
        </div>

        <div
            v-else-if="
                order.payment_method === 'mercadopago' &&
                order.payment_status === 'pending'
            "
            class="mx-auto mt-8 flex max-w-3xl items-start gap-3 rounded-2xl border border-border bg-card p-5 text-sm"
        >
            <CreditCard class="mt-0.5 size-5 shrink-0 text-primary" />
            <p class="text-muted-foreground">
                Estamos esperando la confirmación de MercadoPago. Apenas se
                acredite el pago vas a recibir un email.
            </p>
        </div>

        <div
            class="mx-auto mt-10 grid max-w-5xl gap-6 lg:grid-cols-[1fr_340px]"
        >
            <div class="space-y-6">
                <section
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <h2 class="font-serif text-2xl font-medium">Productos</h2>
                    <ul class="mt-4 divide-y divide-border/70">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div
                                class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-secondary text-muted-foreground"
                            >
                                <Package class="size-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">
                                    {{ item.product_name }}
                                </p>
                                <p
                                    class="mt-0.5 text-[11px] text-muted-foreground"
                                >
                                    Talle {{ item.variant_size }} ·
                                    {{ item.variant_color }} · x{{
                                        item.quantity
                                    }}
                                </p>
                            </div>
                            <p class="text-sm font-medium">
                                {{ formatMoney(item.total_cents) }}
                            </p>
                        </li>
                    </ul>
                </section>

                <section
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <h2 class="font-serif text-2xl font-medium">Seguimiento</h2>
                    <div class="mt-4 space-y-0">
                        <div
                            v-for="(entry, index) in timeline"
                            :key="entry.id"
                            class="relative flex gap-3 pb-5 last:pb-0"
                        >
                            <div class="flex flex-col items-center">
                                <span
                                    :class="[
                                        'mt-1 size-2.5 rounded-full ring-4',
                                        index === timeline.length - 1
                                            ? 'bg-primary ring-primary/15'
                                            : 'bg-border ring-transparent',
                                    ]"
                                />
                                <span
                                    v-if="index < timeline.length - 1"
                                    class="mt-1 w-px flex-1 bg-border"
                                />
                            </div>
                            <div class="pb-1">
                                <p class="text-sm font-medium">
                                    {{ entry.status_label }}
                                </p>
                                <p
                                    v-if="entry.note"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    {{ entry.note }}
                                </p>
                                <p
                                    class="mt-1 text-[10px] text-muted-foreground"
                                >
                                    {{ formatDateTime(entry.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl bg-secondary/70 p-4">
                        <p
                            class="text-[10px] tracking-wide text-muted-foreground uppercase"
                        >
                            Código de seguimiento
                        </p>
                        <p
                            class="mt-1 font-mono text-lg font-semibold tracking-wide"
                        >
                            {{ order.tracking_code }}
                        </p>
                        <Link
                            :href="trackShow(order.tracking_code)"
                            class="mt-2 inline-flex items-center gap-1 text-[11px] font-medium text-primary"
                        >
                            Ver seguimiento público
                            <ArrowRight class="size-3" />
                        </Link>
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="font-serif text-xl font-medium">Resumen</h2>
                    <div class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatMoney(order.subtotal_cents) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Envío</span>
                            <span>{{
                                order.shipping_cost_cents === 0
                                    ? 'Sin cargo'
                                    : formatMoney(order.shipping_cost_cents)
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between border-t border-border/70 pt-2 text-base font-semibold"
                        >
                            <span>Total</span>
                            <span>{{ formatMoney(order.total_cents) }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-[11px] text-muted-foreground">
                        {{ order.payment_method_label }}
                    </p>
                </section>

                <section class="rounded-2xl border border-border bg-card p-5">
                    <h2
                        class="flex items-center gap-2 font-serif text-xl font-medium"
                    >
                        <Truck class="size-4 text-primary" />
                        Entrega
                    </h2>
                    <p class="mt-3 text-sm font-medium">
                        {{ order.shipping_method_label }}
                    </p>
                    <template v-if="order.shipping_method === 'pickup'">
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{
                                order.shipping_zone?.pickup_address ??
                                'Palermo, CABA'
                            }}
                        </p>
                    </template>
                    <template v-else>
                        <p
                            class="mt-1 flex items-start gap-1.5 text-xs text-muted-foreground"
                        >
                            <MapPin class="mt-0.5 size-3 shrink-0" />
                            {{ order.shipping_address }},
                            {{ order.shipping_city }},
                            {{ order.shipping_province }} ({{
                                order.shipping_postal_code
                            }})
                        </p>
                    </template>
                    <p
                        v-if="order.shipping_zone?.delivery_estimate"
                        class="mt-2 text-[11px] text-muted-foreground"
                    >
                        {{ order.shipping_zone.delivery_estimate }}
                    </p>
                </section>

                <section class="rounded-2xl border border-border bg-card p-5">
                    <h2 class="font-serif text-xl font-medium">
                        Datos de contacto
                    </h2>
                    <p class="mt-3 text-sm">
                        {{ order.customer_first_name }}
                        {{ order.customer_last_name }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        {{ order.customer_email }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ order.customer_phone }}
                    </p>
                </section>

                <Link
                    :href="productsIndex()"
                    class="flex h-11 w-full items-center justify-center rounded-full border border-border text-[11px] font-semibold tracking-[0.14em] uppercase transition-colors hover:border-primary/40 hover:text-primary"
                >
                    Seguir comprando
                </Link>
            </aside>
        </div>
    </div>
</template>

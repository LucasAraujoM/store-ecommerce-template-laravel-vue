<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, Package, Search } from '@lucide/vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { formatDateTime, formatMoney } from '@/lib/format';
import { search as trackSearch } from '@/routes/orders/track';
import { index as productsIndex } from '@/routes/products';
import type { Order } from '@/types';

const props = defineProps<{
    code: string;
    order: Order | null;
}>();

const store = usePage().props.store;

const form = useForm({
    codigo: props.code,
});

const timeline = computed(() => {
    if (!props.order) {
        return [];
    }

    return [...props.order.status_histories].sort(
        (a, b) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    );
});

function submit(): void {
    form.post(trackSearch().url, { preserveScroll: true });
}
</script>

<template>
    <Head title="Seguimiento de pedido" />

    <div class="store-container py-10 lg:py-16">
        <div class="mx-auto max-w-2xl text-center">
            <p class="eyebrow">Seguimiento</p>
            <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                ¿Dónde está mi pedido?
            </h1>
            <p class="mt-3 text-sm text-muted-foreground">
                Ingresá tu código de seguimiento para ver el estado del pedido.
            </p>

            <form
                class="mx-auto mt-7 flex max-w-lg gap-2"
                @submit.prevent="submit"
            >
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        v-model="form.codigo"
                        type="text"
                        placeholder="TRK-XXXXXXXXXXXX"
                        class="field-input pl-10 font-mono tracking-wide uppercase"
                        autocomplete="off"
                    />
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="h-11 rounded-full bg-primary px-6 text-[11px] font-semibold tracking-[0.14em] text-primary-foreground uppercase transition-colors hover:bg-primary/90 disabled:opacity-50"
                >
                    {{ form.processing ? 'Buscando…' : 'Buscar' }}
                </button>
            </form>
            <p v-if="form.errors.codigo" class="mt-2 text-xs text-destructive">
                {{ form.errors.codigo }}
            </p>
        </div>

        <div
            v-if="code && !order"
            class="mx-auto mt-8 max-w-2xl rounded-2xl border border-dashed border-border bg-card/60 px-6 py-12 text-center"
        >
            <Package class="mx-auto size-8 text-muted-foreground/50" />
            <h2 class="mt-3 font-serif text-2xl font-medium">
                No encontramos ese pedido
            </h2>
            <p class="mt-2 text-sm text-muted-foreground">
                Revisá el código. Si el problema continúa, escribinos a
                <a
                    :href="`mailto:${store.contact.email}`"
                    class="underline underline-offset-4"
                    >{{ store.contact.email }}</a
                >.
            </p>
        </div>

        <div v-if="order" class="mx-auto mt-10 max-w-4xl">
            <div class="rounded-2xl border border-border bg-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="eyebrow">Pedido</p>
                        <p class="mt-1 font-serif text-2xl font-medium">
                            {{ order.number }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ formatDateTime(order.created_at) }} ·
                            {{ order.customer_email }}
                        </p>
                    </div>
                    <div class="text-right">
                        <StatusBadge
                            :status="order.status"
                            :label="order.status_label"
                        />
                        <p
                            class="mt-2 font-mono text-[11px] text-muted-foreground"
                        >
                            {{ order.tracking_code }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-0">
                    <div
                        v-for="(entry, index) in timeline"
                        :key="entry.id"
                        class="relative flex gap-3 pb-6 last:pb-0"
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
                        <div>
                            <p class="text-sm font-medium">
                                {{ entry.status_label }}
                            </p>
                            <p
                                v-if="entry.note"
                                class="mt-0.5 text-xs text-muted-foreground"
                            >
                                {{ entry.note }}
                            </p>
                            <p class="mt-1 text-[10px] text-muted-foreground">
                                {{ formatDateTime(entry.created_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 grid gap-4 border-t border-border/70 pt-5 sm:grid-cols-2"
                >
                    <div>
                        <p class="eyebrow">Entrega</p>
                        <p class="mt-2 text-sm font-medium">
                            {{ order.shipping_method_label }}
                        </p>
                        <p
                            v-if="order.shipping_method !== 'pickup'"
                            class="mt-1 text-xs leading-relaxed text-muted-foreground"
                        >
                            {{ order.shipping_address }}<br />
                            {{ order.shipping_city }},
                            {{ order.shipping_province }} ({{
                                order.shipping_postal_code
                            }})
                        </p>
                        <p v-else class="mt-1 text-xs text-muted-foreground">
                            {{
                                order.shipping_zone?.pickup_address ??
                                'Palermo, CABA'
                            }}
                        </p>
                        <p
                            v-if="order.shipping_zone?.delivery_estimate"
                            class="mt-1 text-[11px] text-muted-foreground"
                        >
                            {{ order.shipping_zone.delivery_estimate }}
                        </p>
                    </div>
                    <div>
                        <p class="eyebrow">Pago</p>
                        <p class="mt-2 text-sm font-medium">
                            {{ order.payment_method_label }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Total: {{ formatMoney(order.total_cents) }}
                        </p>
                    </div>
                </div>

                <div class="mt-5 border-t border-border/70 pt-5">
                    <p class="eyebrow">Productos</p>
                    <ul class="mt-2 divide-y divide-border/70">
                        <li
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between gap-3 py-2.5 text-sm"
                        >
                            <span class="min-w-0">
                                <span class="block truncate font-medium">{{
                                    item.product_name
                                }}</span>
                                <span class="text-[11px] text-muted-foreground">
                                    Talle {{ item.variant_size }} ·
                                    {{ item.variant_color }} · x{{
                                        item.quantity
                                    }}
                                </span>
                            </span>
                            <span>{{ formatMoney(item.total_cents) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 text-center">
                <Link
                    :href="productsIndex()"
                    class="inline-flex items-center gap-1.5 text-xs font-medium tracking-wide text-primary uppercase"
                >
                    Seguir comprando
                    <ArrowRight class="size-3.5" />
                </Link>
            </div>
        </div>
    </div>
</template>

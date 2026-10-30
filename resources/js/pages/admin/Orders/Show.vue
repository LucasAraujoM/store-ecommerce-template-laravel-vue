<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    MapPin,
    Printer,
    Save,
    Truck,
    User,
} from '@lucide/vue';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    formatDateTime,
    formatMoney,
    orderStatusLabel,
    paymentStatusLabel,
} from '@/lib/format';
import {
    index as adminOrdersIndex,
    status as adminOrderStatus,
} from '@/routes/admin/orders';
import type { Order } from '@/types';

const props = defineProps<{
    order: Order;
    statuses: string[];
}>();

const form = useForm({
    status: props.order.status,
    note: '',
});

const timeline = computed(() =>
    [...props.order.status_histories].sort(
        (a, b) =>
            new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    ),
);

const isCanceled = computed(() => props.order.status === 'canceled');

function submit(): void {
    form.patch(adminOrderStatus(props.order.number).url, {
        preserveScroll: true,
        onSuccess: () => {
            form.note = '';
        },
    });
}

function printOrder(): void {
    window.print();
}
</script>

<template>
    <Head :title="`Pedido ${order.number}`" />

    <div class="admin-container">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link
                    :href="adminOrdersIndex()"
                    class="flex size-9 items-center justify-center rounded-full border border-border bg-card transition-colors hover:border-primary/40"
                    aria-label="Volver"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <p class="eyebrow">Pedido</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="font-serif text-2xl font-medium sm:text-3xl">
                            {{ order.number }}
                        </h1>
                        <StatusBadge
                            :status="order.status"
                            :label="order.status_label"
                        />
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ formatDateTime(order.created_at) }} · Tracking
                        <span class="font-mono">{{ order.tracking_code }}</span>
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-4 text-xs font-medium transition-colors hover:border-primary/40"
                @click="printOrder"
            >
                <Printer class="size-4" />
                Imprimir
            </button>
        </div>

        <div class="mt-6 grid gap-5 xl:grid-cols-[1fr_360px] xl:items-start">
            <div class="space-y-5">
                <section class="admin-card overflow-hidden">
                    <div class="border-b border-border px-5 py-4">
                        <h2 class="font-serif text-xl font-medium">
                            Productos
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead>
                                <tr
                                    class="border-b border-border text-[10px] tracking-wider text-muted-foreground uppercase"
                                >
                                    <th class="px-5 py-2.5 font-medium">
                                        Producto
                                    </th>
                                    <th class="px-4 py-2.5 font-medium">
                                        Variante
                                    </th>
                                    <th
                                        class="px-4 py-2.5 text-center font-medium"
                                    >
                                        Cant.
                                    </th>
                                    <th
                                        class="px-4 py-2.5 text-right font-medium"
                                    >
                                        Precio
                                    </th>
                                    <th
                                        class="px-5 py-2.5 text-right font-medium"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in order.items"
                                    :key="item.id"
                                    class="border-b border-border/60 last:border-b-0"
                                >
                                    <td class="px-5 py-3">
                                        <p class="font-medium">
                                            {{ item.product_name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            {{ item.sku ?? 'Sin SKU' }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-xs text-muted-foreground"
                                    >
                                        {{ item.variant_size }} ·
                                        {{ item.variant_color }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-xs">
                                        {{ formatMoney(item.unit_price_cents) }}
                                    </td>
                                    <td
                                        class="px-5 py-3 text-right font-medium"
                                    >
                                        {{ formatMoney(item.total_cents) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="flex flex-col items-end gap-1 border-t border-border px-5 py-4 text-sm"
                    >
                        <div class="flex w-full max-w-xs justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatMoney(order.subtotal_cents) }}</span>
                        </div>
                        <div class="flex w-full max-w-xs justify-between">
                            <span class="text-muted-foreground">Envío</span>
                            <span>{{
                                order.shipping_cost_cents === 0
                                    ? 'Sin cargo'
                                    : formatMoney(order.shipping_cost_cents)
                            }}</span>
                        </div>
                        <div
                            v-if="order.discount_cents > 0"
                            class="flex w-full max-w-xs justify-between text-emerald-600 dark:text-emerald-400"
                        >
                            <span>Descuento</span>
                            <span
                                >-{{ formatMoney(order.discount_cents) }}</span
                            >
                        </div>
                        <div
                            class="mt-1 flex w-full max-w-xs justify-between border-t border-border pt-2 text-base font-semibold"
                        >
                            <span>Total</span>
                            <span>{{ formatMoney(order.total_cents) }}</span>
                        </div>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Historial</h2>
                    <div class="mt-4">
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
                            <div class="flex-1">
                                <div
                                    class="flex flex-wrap items-center justify-between gap-2"
                                >
                                    <p class="text-sm font-medium">
                                        {{ entry.status_label }}
                                    </p>
                                    <p
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ formatDateTime(entry.created_at) }}
                                    </p>
                                </div>
                                <p
                                    v-if="entry.note"
                                    class="mt-0.5 text-xs text-muted-foreground"
                                >
                                    {{ entry.note }}
                                </p>
                                <p
                                    v-if="entry.author"
                                    class="mt-0.5 text-[10px] text-muted-foreground"
                                >
                                    por {{ entry.author.name }}
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="timeline.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            Sin movimientos registrados.
                        </p>
                    </div>
                </section>

                <section v-if="order.notes" class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">
                        Notas del cliente
                    </h2>
                    <p
                        class="mt-2 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ order.notes }}
                    </p>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">
                        Actualizar estado
                    </h2>

                    <div
                        v-if="isCanceled"
                        class="mt-3 rounded-lg bg-destructive/10 px-3 py-2.5 text-xs text-destructive"
                    >
                        Este pedido está cancelado y no puede volver a otro
                        estado.
                    </div>

                    <form class="mt-4 space-y-3" @submit.prevent="submit">
                        <div>
                            <label class="field-label" for="status"
                                >Estado</label
                            >
                            <select
                                id="status"
                                v-model="form.status"
                                class="field-input"
                                :disabled="isCanceled"
                            >
                                <option
                                    v-for="status in statuses"
                                    :key="status"
                                    :value="status"
                                >
                                    {{ orderStatusLabel(status) }}
                                </option>
                            </select>
                            <p
                                v-if="form.errors.status"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.status }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="note"
                                >Nota
                                <span class="normal-case opacity-60"
                                    >(opcional)</span
                                ></label
                            >
                            <textarea
                                id="note"
                                v-model="form.note"
                                rows="2"
                                class="field-input h-auto py-2.5"
                                placeholder="Ej: despachado por Andreani"
                            />
                            <p
                                v-if="form.errors.note"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.note }}
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing || isCanceled"
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-primary text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:opacity-40"
                        >
                            <Save class="size-4" />
                            {{
                                form.processing
                                    ? 'Guardando…'
                                    : 'Guardar estado'
                            }}
                        </button>
                    </form>

                    <div
                        class="mt-4 space-y-2 border-t border-border/70 pt-4 text-xs"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Pago</span>
                            <span class="font-medium">{{
                                order.payment_method_label
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground"
                                >Estado del pago</span
                            >
                            <span
                                :class="[
                                    'font-medium',
                                    order.payment_status === 'paid'
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400',
                                ]"
                            >
                                {{ paymentStatusLabel(order.payment_status) }}
                            </span>
                        </div>
                        <div
                            v-if="order.paid_at"
                            class="flex items-center justify-between"
                        >
                            <span class="text-muted-foreground"
                                >Acreditado</span
                            >
                            <span>{{ formatDateTime(order.paid_at) }}</span>
                        </div>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <h2
                        class="flex items-center gap-2 font-serif text-xl font-medium"
                    >
                        <User class="size-4 text-primary" />
                        Cliente
                    </h2>
                    <div class="mt-3 space-y-1 text-sm">
                        <p class="font-medium">
                            {{ order.customer_first_name }}
                            {{ order.customer_last_name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ order.customer_email }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ order.customer_phone }}
                        </p>
                        <p
                            v-if="order.customer_dni"
                            class="text-xs text-muted-foreground"
                        >
                            DNI {{ order.customer_dni }}
                        </p>
                    </div>
                    <p
                        v-if="order.user"
                        class="mt-3 border-t border-border/70 pt-3 text-[11px] text-muted-foreground"
                    >
                        Cuenta: {{ order.user.email }}
                    </p>
                </section>

                <section class="admin-card p-5">
                    <h2
                        class="flex items-center gap-2 font-serif text-xl font-medium"
                    >
                        <Truck class="size-4 text-primary" />
                        Entrega
                    </h2>
                    <p class="mt-3 text-sm font-medium">
                        {{ order.shipping_method_label }}
                    </p>
                    <p
                        v-if="order.shipping_method === 'pickup'"
                        class="mt-1 text-xs text-muted-foreground"
                    >
                        {{
                            order.shipping_zone?.pickup_address ??
                            'Palermo, CABA'
                        }}
                    </p>
                    <p
                        v-else
                        class="mt-1 flex items-start gap-1.5 text-xs leading-relaxed text-muted-foreground"
                    >
                        <MapPin class="mt-0.5 size-3 shrink-0" />
                        <span>
                            {{ order.shipping_address }}<br />
                            {{ order.shipping_city }},
                            {{ order.shipping_province }} ({{
                                order.shipping_postal_code
                            }})
                        </span>
                    </p>
                    <p
                        v-if="order.shipping_zone"
                        class="mt-2 text-[11px] text-muted-foreground"
                    >
                        {{ order.shipping_zone.name }}
                        <template v-if="order.shipping_zone.delivery_estimate">
                            · {{ order.shipping_zone.delivery_estimate }}
                        </template>
                    </p>
                </section>

                <div
                    v-if="order.status === 'delivered'"
                    class="flex items-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs text-emerald-700 dark:text-emerald-300"
                >
                    <CheckCircle2 class="size-4" />
                    Pedido entregado correctamente.
                </div>
            </aside>
        </div>
    </div>
</template>

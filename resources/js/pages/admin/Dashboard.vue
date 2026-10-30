<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    Banknote,
    Boxes,
    Plus,
    ShoppingBag,
    Sparkles,
    TriangleAlert,
} from '@lucide/vue';
import StatCard from '@/components/admin/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { formatDate, formatMoney } from '@/lib/format';
import {
    index as adminOrdersIndex,
    show as adminOrderShow,
} from '@/routes/admin/orders';
import { create as adminProductCreate } from '@/routes/admin/products';
import { index as adminProductsIndex } from '@/routes/admin/products';
import { index as adminShippingZonesIndex } from '@/routes/admin/shipping-zones';
import type { AdminStats, Order, ProductVariant } from '@/types';

const props = defineProps<{
    stats: AdminStats;
    recentOrders: Order[];
    lowStock: ProductVariant[];
    topProducts: Array<{ product_name: string; units: number | string }>;
}>();

const maxUnits = computed(() =>
    Math.max(1, ...props.topProducts.map((product) => Number(product.units))),
);
</script>

<template>
    <Head title="Dashboard" />

    <div class="admin-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Resumen general</p>
                <h1 class="mt-1 font-serif text-3xl font-medium sm:text-4xl">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Todo lo que está pasando en Lunaria hoy.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link
                    :href="adminProductCreate()"
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    <Plus class="size-4" />
                    Nuevo producto
                </Link>
                <Link
                    :href="adminOrdersIndex()"
                    class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-4 text-xs font-medium transition-colors hover:border-primary/40"
                >
                    Ver pedidos
                    <ArrowRight class="size-3.5" />
                </Link>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard
                label="Ventas cobradas"
                :value="formatMoney(stats.revenue_cents)"
                hint="Pedidos con pago acreditado"
                :icon="Banknote"
            />
            <StatCard
                label="Pedidos hoy"
                :value="String(stats.orders_today)"
                :hint="`${stats.orders_total} pedidos en total`"
                :icon="ShoppingBag"
            />
            <StatCard
                label="Pendientes"
                :value="String(stats.orders_pending)"
                hint="Esperando pago o preparación"
                :icon="Sparkles"
            />
            <StatCard
                label="Stock bajo"
                :value="String(stats.low_stock_count)"
                hint="Variantes con 3 unidades o menos"
                :icon="TriangleAlert"
            />
        </div>

        <div class="mt-6 grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <section class="admin-card overflow-hidden">
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-4"
                >
                    <h2 class="font-serif text-xl font-medium">
                        Últimos pedidos
                    </h2>
                    <Link
                        :href="adminOrdersIndex()"
                        class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase transition-colors hover:text-primary"
                    >
                        Ver todos
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-border text-[10px] tracking-wider text-muted-foreground uppercase"
                            >
                                <th class="px-5 py-3 font-medium">Pedido</th>
                                <th class="px-4 py-3 font-medium">Cliente</th>
                                <th class="px-4 py-3 font-medium">Fecha</th>
                                <th class="px-4 py-3 font-medium">Estado</th>
                                <th class="px-5 py-3 text-right font-medium">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="border-b border-border/60 last:border-b-0 hover:bg-secondary/40"
                            >
                                <td class="px-5 py-3">
                                    <Link
                                        :href="adminOrderShow(order.number)"
                                        class="font-medium transition-colors hover:text-primary"
                                    >
                                        {{ order.number }}
                                    </Link>
                                    <p
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ order.items_count ?? 0 }} artículos
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-sm">
                                        {{ order.customer_first_name }}
                                        {{ order.customer_last_name }}
                                    </p>
                                    <p
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ order.customer_email }}
                                    </p>
                                </td>
                                <td
                                    class="px-4 py-3 text-xs text-muted-foreground"
                                >
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge
                                        :status="order.status"
                                        :label="order.status_label"
                                    />
                                </td>
                                <td
                                    class="px-5 py-3 text-right text-sm font-semibold"
                                >
                                    {{ formatMoney(order.total_cents) }}
                                </td>
                            </tr>
                            <tr v-if="recentOrders.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-10 text-center text-sm text-muted-foreground"
                                >
                                    Todavía no hay pedidos.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="space-y-5">
                <section class="admin-card p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-serif text-xl font-medium">
                            Stock bajo
                        </h2>
                        <Link
                            :href="adminProductsIndex()"
                            class="text-[11px] text-muted-foreground uppercase hover:text-primary"
                        >
                            Productos
                        </Link>
                    </div>
                    <ul class="mt-4 space-y-3">
                        <li
                            v-for="variant in lowStock"
                            :key="variant.id"
                            class="flex items-center justify-between gap-3 border-b border-border/60 pb-3 last:border-b-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ variant.product?.name ?? 'Producto' }}
                                </p>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ variant.label }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                    variant.stock === 0
                                        ? 'bg-red-500/10 text-red-700 dark:text-red-300'
                                        : 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                ]"
                            >
                                {{ variant.stock }} u.
                            </span>
                        </li>
                        <li
                            v-if="lowStock.length === 0"
                            class="py-4 text-center text-xs text-muted-foreground"
                        >
                            Todo el stock está en orden.
                        </li>
                    </ul>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Más vendidos</h2>
                    <ul class="mt-4 space-y-3">
                        <li
                            v-for="product in topProducts"
                            :key="product.product_name"
                        >
                            <div
                                class="flex items-center justify-between gap-2 text-xs"
                            >
                                <span class="truncate font-medium">{{
                                    product.product_name
                                }}</span>
                                <span class="shrink-0 text-muted-foreground"
                                    >{{ product.units }} u.</span
                                >
                            </div>
                            <div
                                class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-secondary"
                            >
                                <div
                                    class="h-full rounded-full bg-primary/70"
                                    :style="{
                                        width: `${(Number(product.units) / maxUnits) * 100}%`,
                                    }"
                                />
                            </div>
                        </li>
                        <li
                            v-if="topProducts.length === 0"
                            class="py-4 text-center text-xs text-muted-foreground"
                        >
                            Aún no hay ventas registradas.
                        </li>
                    </ul>
                </section>
            </div>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <Link
                :href="adminProductCreate()"
                class="admin-card flex items-center gap-3 p-4 transition-colors hover:border-primary/40"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-accent text-primary"
                    ><Boxes class="size-4"
                /></span>
                <span>
                    <span class="block text-sm font-medium"
                        >Cargar producto</span
                    >
                    <span class="block text-[11px] text-muted-foreground"
                        >Fotos, talles y stock</span
                    >
                </span>
            </Link>
            <Link
                :href="adminShippingZonesIndex()"
                class="admin-card flex items-center gap-3 p-4 transition-colors hover:border-primary/40"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-accent text-primary"
                    ><Sparkles class="size-4"
                /></span>
                <span>
                    <span class="block text-sm font-medium"
                        >Zonas de envío</span
                    >
                    <span class="block text-[11px] text-muted-foreground"
                        >Tarifas y retiro</span
                    >
                </span>
            </Link>
            <Link
                :href="adminOrdersIndex()"
                class="admin-card flex items-center gap-3 p-4 transition-colors hover:border-primary/40"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-accent text-primary"
                    ><ShoppingBag class="size-4"
                /></span>
                <span>
                    <span class="block text-sm font-medium"
                        >Gestionar pedidos</span
                    >
                    <span class="block text-[11px] text-muted-foreground"
                        >Estados y seguimiento</span
                    >
                </span>
            </Link>
        </div>
    </div>
</template>

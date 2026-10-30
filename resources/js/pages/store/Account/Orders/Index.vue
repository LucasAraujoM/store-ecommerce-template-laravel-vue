<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Package } from '@lucide/vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { formatDate, formatMoney } from '@/lib/format';
import {
    index as accountOrdersIndex,
    show as accountOrdersShow,
} from '@/routes/account/orders';
import { index as productsIndex } from '@/routes/products';
import type { Order, Paginated } from '@/types';

defineProps<{
    orders: Paginated<Order>;
}>();
</script>

<template>
    <Head title="Mis pedidos" />

    <div class="store-container py-10 lg:py-14">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Tu cuenta</p>
                <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                    Mis pedidos
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Historial de compras y estado de cada envío.
                </p>
            </div>
            <Link
                :href="accountOrdersIndex()"
                class="text-xs tracking-[0.14em] text-muted-foreground uppercase"
            >
                Actualizar
            </Link>
        </div>

        <div v-if="orders.data.length" class="mt-8 space-y-3">
            <div
                v-for="order in orders.data"
                :key="order.id"
                class="rounded-2xl border border-border bg-card p-4 sm:p-5"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-11 items-center justify-center rounded-full bg-accent text-primary"
                        >
                            <Package class="size-5" />
                        </div>
                        <div>
                            <p class="font-serif text-xl font-medium">
                                {{ order.number }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ formatDate(order.created_at) }} ·
                                {{ order.items_count ?? 0 }}
                                {{
                                    (order.items_count ?? 0) === 1
                                        ? 'artículo'
                                        : 'artículos'
                                }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge
                                    :status="order.status"
                                    :label="order.status_label"
                                />
                                <span class="text-[11px] text-muted-foreground">
                                    {{ order.payment_method_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <p class="font-serif text-xl font-semibold">
                            {{ formatMoney(order.total_cents) }}
                        </p>
                        <Link
                            :href="accountOrdersShow(order.number)"
                            class="inline-flex h-9 items-center gap-1.5 rounded-full border border-border px-4 text-[11px] font-semibold tracking-[0.12em] uppercase transition-colors hover:border-primary/40 hover:text-primary"
                        >
                            Ver detalle
                            <ArrowRight class="size-3.5" />
                        </Link>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <Pagination
                    :links="orders.links"
                    :from="orders.from"
                    :to="orders.to"
                    :total="orders.total"
                />
            </div>
        </div>

        <div
            v-else
            class="mt-8 rounded-2xl border border-dashed border-border bg-card/60 px-6 py-16 text-center"
        >
            <Package class="mx-auto size-9 text-muted-foreground/40" />
            <h2 class="mt-4 font-serif text-2xl font-medium">
                Todavía no tenés pedidos
            </h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-muted-foreground">
                Cuando hagas tu primera compra, vas a poder seguirla desde acá.
            </p>
            <Link
                :href="productsIndex()"
                class="mt-6 inline-flex h-11 items-center gap-2 rounded-full bg-primary px-6 text-[11px] font-semibold tracking-[0.14em] text-primary-foreground uppercase"
            >
                Ver la colección
                <ArrowRight class="size-4" />
            </Link>
        </div>
    </div>
</template>

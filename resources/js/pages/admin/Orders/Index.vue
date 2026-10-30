<script setup lang="ts">
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Search } from '@lucide/vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import {
    formatDateTime,
    formatMoney,
    paymentStatusLabel,
    orderStatusLabel,
} from '@/lib/format';
import {
    index as adminOrdersIndex,
    show as adminOrderShow,
} from '@/routes/admin/orders';
import type { Order, Paginated } from '@/types';

const props = defineProps<{
    orders: Paginated<Order>;
    filters: {
        q: string;
        status: string;
    };
    statuses: string[];
}>();

const filters = reactive({ ...props.filters });

function apply(): void {
    const params: Record<string, string> = {};

    if (filters.q.trim() !== '') {
        params.q = filters.q.trim();
    }

    if (filters.status !== '') {
        params.status = filters.status;
    }

    router.get(adminOrdersIndex().url, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function reset(): void {
    filters.q = '';
    filters.status = '';
    router.get(
        adminOrdersIndex().url,
        {},
        { preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head title="Pedidos" />

    <div class="admin-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Ventas</p>
                <h1 class="mt-1 font-serif text-3xl font-medium sm:text-4xl">
                    Pedidos
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ orders.total }} pedidos · actualizá el estado y seguí
                    cada envío.
                </p>
            </div>
        </div>

        <form
            class="admin-card mt-6 flex flex-wrap items-end gap-3 p-4"
            @submit.prevent="apply"
        >
            <div class="min-w-[220px] flex-1">
                <label class="field-label" for="q">Buscar</label>
                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-muted-foreground"
                    />
                    <input
                        id="q"
                        v-model="filters.q"
                        type="search"
                        placeholder="Número, tracking, email o apellido"
                        class="field-input field-input-sm pl-9"
                    />
                </div>
            </div>
            <div class="w-full sm:w-52">
                <label class="field-label" for="status">Estado</label>
                <select
                    id="status"
                    v-model="filters.status"
                    class="field-input field-input-sm"
                >
                    <option value="">Todos</option>
                    <option
                        v-for="status in statuses"
                        :key="status"
                        :value="status"
                    >
                        {{ orderStatusLabel(status) }}
                    </option>
                </select>
            </div>
            <button
                type="submit"
                class="h-9 rounded-md bg-primary px-4 text-xs font-medium text-primary-foreground hover:bg-primary/90"
            >
                Filtrar
            </button>
            <button
                type="button"
                class="h-9 rounded-md border border-border px-3 text-xs text-muted-foreground hover:text-foreground"
                @click="reset"
            >
                Limpiar
            </button>
        </form>

        <div class="admin-card mt-5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-border text-[10px] tracking-wider text-muted-foreground uppercase"
                        >
                            <th class="px-4 py-3 font-medium">Pedido</th>
                            <th class="px-4 py-3 font-medium">Cliente</th>
                            <th class="px-4 py-3 font-medium">Fecha</th>
                            <th class="px-4 py-3 font-medium">Pago</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Total
                            </th>
                            <th class="px-4 py-3 text-right font-medium">
                                Ver
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="border-b border-border/60 last:border-b-0 hover:bg-secondary/30"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="adminOrderShow(order.number)"
                                    class="font-medium transition-colors hover:text-primary"
                                >
                                    {{ order.number }}
                                </Link>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ order.items_count ?? 0 }} artículos
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <p>
                                    {{ order.customer_first_name }}
                                    {{ order.customer_last_name }}
                                </p>
                                <p class="text-[10px] text-muted-foreground">
                                    {{ order.customer_email }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ formatDateTime(order.created_at) }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-xs">
                                    {{ order.payment_method_label }}
                                </p>
                                <p
                                    :class="[
                                        'text-[10px] font-medium',
                                        order.payment_status === 'paid'
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-amber-600 dark:text-amber-400',
                                    ]"
                                >
                                    {{
                                        paymentStatusLabel(order.payment_status)
                                    }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <StatusBadge
                                    :status="order.status"
                                    :label="order.status_label"
                                />
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                {{ formatMoney(order.total_cents) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <Link
                                        :href="adminOrderShow(order.number)"
                                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                                        aria-label="Ver pedido"
                                    >
                                        <Eye class="size-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="orders.data.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                No hay pedidos con esos filtros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">
            <Pagination
                :links="orders.links"
                :from="orders.from"
                :to="orders.to"
                :total="orders.total"
            />
        </div>
    </div>
</template>

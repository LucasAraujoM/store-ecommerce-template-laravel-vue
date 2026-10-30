<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, Pencil, Plus, Store, Trash2, Truck } from '@lucide/vue';
import { formatMoney } from '@/lib/format';
import {
    create as adminShippingZoneCreate,
    destroy as adminShippingZoneDestroy,
    edit as adminShippingZoneEdit,
} from '@/routes/admin/shipping-zones';
import type { ShippingZone } from '@/types';

defineProps<{
    zones: ShippingZone[];
}>();

function destroy(zone: ShippingZone): void {
    if (!window.confirm(`¿Eliminar la zona “${zone.name}”?`)) {
        return;
    }

    router.delete(adminShippingZoneDestroy(zone.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Zonas de envío" />

    <div class="admin-container">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Logística</p>
                <h1 class="mt-1 font-serif text-3xl font-medium sm:text-4xl">
                    Zonas de envío
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Tarifas por zona, envío gratis y puntos de retiro.
                </p>
            </div>
            <Link
                :href="adminShippingZoneCreate()"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                <Plus class="size-4" />
                Nueva zona
            </Link>
        </div>

        <div class="admin-card mt-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[940px] text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-border text-[10px] tracking-wider text-muted-foreground uppercase"
                        >
                            <th class="px-4 py-3 font-medium">Zona</th>
                            <th class="px-4 py-3 font-medium">Tipo</th>
                            <th class="px-4 py-3 font-medium">Provincias</th>
                            <th class="px-4 py-3 font-medium">Costo</th>
                            <th class="px-4 py-3 font-medium">Gratis desde</th>
                            <th class="px-4 py-3 font-medium">Demora</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3 text-right font-medium">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="zone in zones"
                            :key="zone.id"
                            class="border-b border-border/60 last:border-b-0 hover:bg-secondary/30"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-md bg-accent text-primary"
                                    >
                                        <Store
                                            v-if="zone.is_pickup"
                                            class="size-3.5"
                                        />
                                        <Truck v-else class="size-3.5" />
                                    </span>
                                    <div>
                                        <p class="font-medium">
                                            {{ zone.name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            Orden {{ zone.sort_order }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase',
                                        zone.is_pickup
                                            ? 'bg-sky-500/10 text-sky-700 dark:text-sky-300'
                                            : 'bg-primary/10 text-primary',
                                    ]"
                                >
                                    {{ zone.is_pickup ? 'Retiro' : 'Envío' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <template v-if="zone.is_pickup">
                                    <span class="flex items-start gap-1">
                                        <MapPin
                                            class="mt-0.5 size-3 shrink-0"
                                        />
                                        {{ zone.pickup_address ?? '—' }}
                                    </span>
                                </template>
                                <template v-else>
                                    {{
                                        zone.provinces?.length
                                            ? zone.provinces.join(', ')
                                            : 'Todas'
                                    }}
                                </template>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                {{
                                    zone.cost_cents === 0
                                        ? 'Sin cargo'
                                        : formatMoney(zone.cost_cents)
                                }}
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{
                                    zone.free_from_cents
                                        ? formatMoney(zone.free_from_cents)
                                        : '—'
                                }}
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                {{ zone.delivery_estimate ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase',
                                        zone.is_active
                                            ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300'
                                            : 'bg-secondary text-muted-foreground',
                                    ]"
                                >
                                    {{ zone.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <Link
                                        :href="adminShippingZoneEdit(zone.id)"
                                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                                        aria-label="Editar"
                                    >
                                        <Pencil class="size-3.5" />
                                    </Link>
                                    <button
                                        type="button"
                                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        aria-label="Eliminar"
                                        @click="destroy(zone)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="zones.length === 0">
                            <td
                                colspan="8"
                                class="px-4 py-12 text-center text-sm text-muted-foreground"
                            >
                                Todavía no hay zonas de envío.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

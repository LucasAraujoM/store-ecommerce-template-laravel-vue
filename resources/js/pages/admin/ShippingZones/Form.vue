<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Store, Truck } from '@lucide/vue';
import {
    index as adminShippingZonesIndex,
    store as adminShippingZoneStore,
    update as adminShippingZoneUpdate,
} from '@/routes/admin/shipping-zones';
import type { ShippingZone } from '@/types';

const props = defineProps<{
    zone: ShippingZone | null;
}>();

const form = useForm({
    name: props.zone?.name ?? '',
    provinces: props.zone?.provinces?.join(', ') ?? '',
    cost: props.zone ? props.zone.cost_cents / 100 : 0,
    free_from: props.zone?.free_from_cents
        ? props.zone.free_from_cents / 100
        : '',
    delivery_estimate: props.zone?.delivery_estimate ?? '',
    is_pickup: props.zone?.is_pickup ?? false,
    pickup_address: props.zone?.pickup_address ?? '',
    is_active: props.zone?.is_active ?? true,
    sort_order: props.zone?.sort_order ?? 0,
});

const isEditing = computed(() => props.zone !== null);

watch(
    () => form.is_pickup,
    (isPickup) => {
        if (isPickup) {
            form.cost = 0;
            form.free_from = '';
        }
    },
);

function submit(): void {
    const options = { preserveScroll: true };

    if (props.zone) {
        form.put(adminShippingZoneUpdate(props.zone.id).url, options);

        return;
    }

    form.post(adminShippingZoneStore().url, options);
}
</script>

<template>
    <Head :title="isEditing ? `Editar ${zone?.name}` : 'Nueva zona de envío'" />

    <div class="admin-container">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <Link
                    :href="adminShippingZonesIndex()"
                    class="flex size-9 items-center justify-center rounded-full border border-border bg-card transition-colors hover:border-primary/40"
                    aria-label="Volver"
                >
                    <ArrowLeft class="size-4" />
                </Link>
                <div>
                    <p class="eyebrow">Logística</p>
                    <h1
                        class="mt-0.5 font-serif text-2xl font-medium sm:text-3xl"
                    >
                        {{ isEditing ? 'Editar zona' : 'Nueva zona de envío' }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Link
                    :href="adminShippingZonesIndex()"
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
            class="mt-6 grid gap-5 lg:grid-cols-[1fr_320px] lg:items-start"
            @submit.prevent="submit"
        >
            <div class="space-y-5">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">
                        Datos de la zona
                    </h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="name">Nombre</label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="field-input"
                                placeholder="Ej: CABA, GBA, Interior"
                            />
                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="field-label" for="provinces"
                                >Provincias</label
                            >
                            <input
                                id="provinces"
                                v-model="form.provinces"
                                type="text"
                                class="field-input"
                                placeholder="CABA, Buenos Aires"
                            />
                            <p class="mt-1 text-[10px] text-muted-foreground">
                                Separadas por coma. Dejalo vacío para que
                                aplique a todas.
                            </p>
                            <p
                                v-if="form.errors.provinces"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.provinces }}
                            </p>
                        </div>

                        <div v-if="!form.is_pickup">
                            <label class="field-label" for="cost"
                                >Costo del envío (ARS)</label
                            >
                            <input
                                id="cost"
                                v-model.number="form.cost"
                                type="number"
                                min="0"
                                step="100"
                                class="field-input"
                            />
                            <p
                                v-if="form.errors.cost"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.cost }}
                            </p>
                        </div>

                        <div v-if="!form.is_pickup">
                            <label class="field-label" for="free_from"
                                >Envío gratis desde (ARS)</label
                            >
                            <input
                                id="free_from"
                                v-model="form.free_from"
                                type="number"
                                min="0"
                                step="100"
                                class="field-input"
                                placeholder="Opcional"
                            />
                            <p
                                v-if="form.errors.free_from"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.free_from }}
                            </p>
                        </div>

                        <div>
                            <label class="field-label" for="delivery_estimate"
                                >Demora estimada</label
                            >
                            <input
                                id="delivery_estimate"
                                v-model="form.delivery_estimate"
                                type="text"
                                class="field-input"
                                placeholder="Ej: 3 a 5 días hábiles"
                            />
                            <p
                                v-if="form.errors.delivery_estimate"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.delivery_estimate }}
                            </p>
                        </div>

                        <div>
                            <label class="field-label" for="sort_order"
                                >Orden</label
                            >
                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="field-input"
                            />
                            <p
                                v-if="form.errors.sort_order"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.sort_order }}
                            </p>
                        </div>

                        <div v-if="form.is_pickup" class="sm:col-span-2">
                            <label class="field-label" for="pickup_address"
                                >Dirección de retiro</label
                            >
                            <input
                                id="pickup_address"
                                v-model="form.pickup_address"
                                type="text"
                                class="field-input"
                                placeholder="Ej: Av. Santa Fe 1234, Palermo"
                            />
                            <p
                                v-if="form.errors.pickup_address"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.pickup_address }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="space-y-4">
                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">
                        Tipo de entrega
                    </h2>
                    <div class="mt-4 space-y-2">
                        <button
                            type="button"
                            :class="[
                                'flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-colors',
                                !form.is_pickup
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                            ]"
                            @click="form.is_pickup = false"
                        >
                            <Truck class="mt-0.5 size-4 text-primary" />
                            <span>
                                <span class="block text-xs font-medium"
                                    >Envío a domicilio</span
                                >
                                <span
                                    class="block text-[10px] text-muted-foreground"
                                    >Tarifa según zona</span
                                >
                            </span>
                        </button>
                        <button
                            type="button"
                            :class="[
                                'flex w-full items-start gap-3 rounded-lg border p-3 text-left transition-colors',
                                form.is_pickup
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                            ]"
                            @click="form.is_pickup = true"
                        >
                            <Store class="mt-0.5 size-4 text-primary" />
                            <span>
                                <span class="block text-xs font-medium"
                                    >Retiro en local</span
                                >
                                <span
                                    class="block text-[10px] text-muted-foreground"
                                    >Sin costo para el cliente</span
                                >
                            </span>
                        </button>
                    </div>
                </section>

                <section class="admin-card p-5">
                    <h2 class="font-serif text-xl font-medium">Publicación</h2>
                    <label
                        class="mt-4 flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5"
                    >
                        <span>
                            <span class="block text-xs font-medium"
                                >Activa</span
                            >
                            <span
                                class="block text-[10px] text-muted-foreground"
                                >Disponible en checkout</span
                            >
                        </span>
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="size-4 accent-[var(--primary)]"
                        />
                    </label>
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
                              : 'Crear zona'
                    }}
                </button>
            </aside>
        </form>
    </div>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    CreditCard,
    Landmark,
    LockKeyhole,
    Store,
    Truck,
} from '@lucide/vue';
import { formatMoney } from '@/lib/format';
import { store as checkoutStore } from '@/routes/checkout';
import { index as productsIndex } from '@/routes/products';
import type { CartSummary, ShippingZone } from '@/types';

const props = defineProps<{
    cart: CartSummary;
    zones: ShippingZone[];
    mercadopagoEnabled: boolean;
    customer: {
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        dni: string;
    };
}>();

const homeZones = computed(() => props.zones.filter((zone) => !zone.is_pickup));
const pickupZones = computed(() =>
    props.zones.filter((zone) => zone.is_pickup),
);

const form = useForm({
    customer_first_name: props.customer.first_name,
    customer_last_name: props.customer.last_name,
    customer_email: props.customer.email,
    customer_phone: props.customer.phone,
    customer_dni: props.customer.dni,
    shipping_method: 'home_delivery',
    shipping_zone_id: homeZones.value[0]?.id ?? pickupZones.value[0]?.id ?? '',
    shipping_address: '',
    shipping_city: '',
    shipping_province: '',
    shipping_postal_code: '',
    payment_method: props.mercadopagoEnabled ? 'mercadopago' : 'transfer',
    notes: '',
    terms: false,
});

const availableZones = computed(() =>
    form.shipping_method === 'pickup' ? pickupZones.value : homeZones.value,
);

watch(
    () => form.shipping_method,
    () => {
        form.shipping_zone_id = availableZones.value[0]?.id ?? '';
    },
);

const selectedZone = computed(
    () =>
        props.zones.find((zone) => zone.id === Number(form.shipping_zone_id)) ??
        null,
);

const shippingCost = computed(() => {
    if (form.shipping_method === 'pickup') {
        return 0;
    }

    const zone = selectedZone.value;

    if (!zone) {
        return 0;
    }

    if (
        zone.free_from_cents !== null &&
        props.cart.subtotal_cents >= zone.free_from_cents
    ) {
        return 0;
    }

    return zone.cost_cents;
});

const total = computed(() => props.cart.subtotal_cents + shippingCost.value);

const generalError = computed(
    () => (form.errors as Record<string, string | undefined>).cart ?? null,
);

function submit(): void {
    form.post(checkoutStore().url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Checkout" />

    <div class="store-container py-8 lg:py-12">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Último paso</p>
                <h1 class="mt-2 font-serif text-4xl font-medium sm:text-5xl">
                    Finalizar compra
                </h1>
            </div>
            <Link
                :href="productsIndex()"
                class="link-underline text-xs tracking-[0.14em] uppercase"
            >
                Seguir comprando
            </Link>
        </div>

        <div
            v-if="generalError"
            class="mt-6 rounded-lg border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive"
        >
            {{ generalError }}
        </div>

        <form
            class="mt-8 grid gap-10 lg:grid-cols-[1fr_400px] lg:items-start"
            @submit.prevent="submit"
        >
            <div class="space-y-6">
                <section
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-6 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                            >1</span
                        >
                        <h2 class="font-serif text-2xl font-medium">
                            Tus datos
                        </h2>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="first_name"
                                >Nombre</label
                            >
                            <input
                                id="first_name"
                                v-model="form.customer_first_name"
                                type="text"
                                class="field-input"
                                autocomplete="given-name"
                            />
                            <p
                                v-if="form.errors.customer_first_name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.customer_first_name }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="last_name"
                                >Apellido</label
                            >
                            <input
                                id="last_name"
                                v-model="form.customer_last_name"
                                type="text"
                                class="field-input"
                                autocomplete="family-name"
                            />
                            <p
                                v-if="form.errors.customer_last_name"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.customer_last_name }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="email">Email</label>
                            <input
                                id="email"
                                v-model="form.customer_email"
                                type="email"
                                class="field-input"
                                autocomplete="email"
                            />
                            <p
                                v-if="form.errors.customer_email"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.customer_email }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="phone"
                                >Teléfono</label
                            >
                            <input
                                id="phone"
                                v-model="form.customer_phone"
                                type="tel"
                                class="field-input"
                                autocomplete="tel"
                                placeholder="+54 11 ..."
                            />
                            <p
                                v-if="form.errors.customer_phone"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.customer_phone }}
                            </p>
                        </div>
                        <div class="sm:col-span-2 sm:max-w-xs">
                            <label class="field-label" for="dni"
                                >DNI
                                <span class="normal-case opacity-60"
                                    >(opcional)</span
                                ></label
                            >
                            <input
                                id="dni"
                                v-model="form.customer_dni"
                                type="text"
                                inputmode="numeric"
                                class="field-input"
                            />
                            <p
                                v-if="form.errors.customer_dni"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.customer_dni }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-6 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                            >2</span
                        >
                        <h2 class="font-serif text-2xl font-medium">Entrega</h2>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <button
                            type="button"
                            :class="[
                                'flex items-start gap-3 rounded-xl border p-4 text-left transition-colors',
                                form.shipping_method === 'home_delivery'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                            ]"
                            @click="form.shipping_method = 'home_delivery'"
                        >
                            <Truck class="mt-0.5 size-5 text-primary" />
                            <span>
                                <span class="block text-sm font-medium"
                                    >Envío a domicilio</span
                                >
                                <span
                                    class="mt-0.5 block text-xs text-muted-foreground"
                                >
                                    {{
                                        homeZones.length
                                            ? `Desde ${formatMoney(homeZones[0].cost_cents)}`
                                            : 'Consultar'
                                    }}
                                </span>
                            </span>
                        </button>

                        <button
                            type="button"
                            :disabled="pickupZones.length === 0"
                            :class="[
                                'flex items-start gap-3 rounded-xl border p-4 text-left transition-colors disabled:cursor-not-allowed disabled:opacity-40',
                                form.shipping_method === 'pickup'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                            ]"
                            @click="form.shipping_method = 'pickup'"
                        >
                            <Store class="mt-0.5 size-5 text-primary" />
                            <span>
                                <span class="block text-sm font-medium"
                                    >Retiro en el local</span
                                >
                                <span
                                    class="mt-0.5 block text-xs text-muted-foreground"
                                    >Sin cargo · Palermo</span
                                >
                            </span>
                        </button>
                    </div>

                    <div class="mt-5">
                        <label class="field-label" for="zone">
                            {{
                                form.shipping_method === 'pickup'
                                    ? 'Punto de retiro'
                                    : 'Zona de envío'
                            }}
                        </label>
                        <select
                            id="zone"
                            v-model="form.shipping_zone_id"
                            class="field-input"
                        >
                            <option
                                v-for="zone in availableZones"
                                :key="zone.id"
                                :value="zone.id"
                            >
                                {{ zone.name }}
                                <template
                                    v-if="
                                        form.shipping_method === 'home_delivery'
                                    "
                                >
                                    — {{ formatMoney(zone.cost_cents) }}
                                </template>
                            </option>
                        </select>
                        <p
                            v-if="form.errors.shipping_zone_id"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.shipping_zone_id }}
                        </p>

                        <p
                            v-if="selectedZone?.delivery_estimate"
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            {{ selectedZone.delivery_estimate }}
                        </p>
                        <p
                            v-if="
                                form.shipping_method === 'pickup' &&
                                selectedZone?.pickup_address
                            "
                            class="mt-2 text-xs text-muted-foreground"
                        >
                            {{ selectedZone.pickup_address }}
                        </p>
                    </div>

                    <div
                        v-if="form.shipping_method === 'home_delivery'"
                        class="mt-5 grid gap-4 sm:grid-cols-2"
                    >
                        <div class="sm:col-span-2">
                            <label class="field-label" for="address"
                                >Dirección y número</label
                            >
                            <input
                                id="address"
                                v-model="form.shipping_address"
                                type="text"
                                class="field-input"
                                autocomplete="street-address"
                            />
                            <p
                                v-if="form.errors.shipping_address"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.shipping_address }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="city"
                                >Localidad</label
                            >
                            <input
                                id="city"
                                v-model="form.shipping_city"
                                type="text"
                                class="field-input"
                                autocomplete="address-level2"
                            />
                            <p
                                v-if="form.errors.shipping_city"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.shipping_city }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" for="province"
                                >Provincia</label
                            >
                            <input
                                id="province"
                                v-model="form.shipping_province"
                                type="text"
                                class="field-input"
                                autocomplete="address-level1"
                            />
                            <p
                                v-if="form.errors.shipping_province"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.shipping_province }}
                            </p>
                        </div>
                        <div class="sm:max-w-[160px]">
                            <label class="field-label" for="postal_code"
                                >Código postal</label
                            >
                            <input
                                id="postal_code"
                                v-model="form.shipping_postal_code"
                                type="text"
                                class="field-input"
                                autocomplete="postal-code"
                            />
                            <p
                                v-if="form.errors.shipping_postal_code"
                                class="mt-1 text-xs text-destructive"
                            >
                                {{ form.errors.shipping_postal_code }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label class="field-label" for="notes"
                            >Notas para el pedido
                            <span class="normal-case opacity-60"
                                >(opcional)</span
                            ></label
                        >
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="field-input h-auto py-2.5"
                            placeholder="Timbre, horario de entrega, comentarios..."
                        />
                        <p
                            v-if="form.errors.notes"
                            class="mt-1 text-xs text-destructive"
                        >
                            {{ form.errors.notes }}
                        </p>
                    </div>
                </section>

                <section
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="flex size-6 items-center justify-center rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                            >3</span
                        >
                        <h2 class="font-serif text-2xl font-medium">Pago</h2>
                    </div>

                    <div class="mt-5 space-y-3">
                        <label
                            :class="[
                                'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors',
                                form.payment_method === 'mercadopago'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                                !mercadopagoEnabled
                                    ? 'cursor-not-allowed opacity-50'
                                    : '',
                            ]"
                        >
                            <input
                                v-model="form.payment_method"
                                type="radio"
                                value="mercadopago"
                                class="mt-1 accent-[var(--primary)]"
                                :disabled="!mercadopagoEnabled"
                            />
                            <CreditCard class="mt-0.5 size-5 text-primary" />
                            <span class="flex-1">
                                <span
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    MercadoPago
                                    <span
                                        v-if="!mercadopagoEnabled"
                                        class="rounded-full bg-secondary px-2 py-0.5 text-[9px] uppercase"
                                        >Próximamente</span
                                    >
                                </span>
                                <span
                                    class="mt-0.5 block text-xs text-muted-foreground"
                                >
                                    Tarjetas de crédito, débito y dinero en
                                    cuenta.
                                </span>
                            </span>
                        </label>

                        <label
                            :class="[
                                'flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors',
                                form.payment_method === 'transfer'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-primary/30',
                            ]"
                        >
                            <input
                                v-model="form.payment_method"
                                type="radio"
                                value="transfer"
                                class="mt-1 accent-[var(--primary)]"
                            />
                            <Landmark class="mt-0.5 size-5 text-primary" />
                            <span class="flex-1">
                                <span class="block text-sm font-medium"
                                    >Transferencia bancaria</span
                                >
                                <span
                                    class="mt-0.5 block text-xs text-muted-foreground"
                                >
                                    Te mostramos los datos al confirmar el
                                    pedido.
                                </span>
                            </span>
                        </label>
                    </div>

                    <p
                        v-if="form.errors.payment_method"
                        class="mt-2 text-xs text-destructive"
                    >
                        {{ form.errors.payment_method }}
                    </p>

                    <label
                        class="mt-5 flex items-start gap-2.5 text-xs text-muted-foreground"
                    >
                        <input
                            v-model="form.terms"
                            type="checkbox"
                            class="mt-0.5 accent-[var(--primary)]"
                        />
                        <span>
                            Acepto los términos y condiciones y la política de
                            privacidad de Lunaria.
                        </span>
                    </label>
                    <p
                        v-if="form.errors.terms"
                        class="mt-1 text-xs text-destructive"
                    >
                        {{ form.errors.terms }}
                    </p>
                </section>
            </div>

            <aside class="lg:sticky lg:top-28">
                <div
                    class="rounded-2xl border border-border bg-card p-5 sm:p-6"
                >
                    <h2 class="font-serif text-2xl font-medium">Tu pedido</h2>

                    <ul class="mt-4 max-h-72 space-y-3 overflow-y-auto pr-1">
                        <li
                            v-for="line in cart.items"
                            :key="line.id"
                            class="flex gap-3"
                        >
                            <div
                                class="relative size-14 shrink-0 overflow-hidden rounded-md bg-secondary"
                            >
                                <img
                                    v-if="line.product_image"
                                    :src="line.product_image"
                                    :alt="line.product_name"
                                    class="h-full w-full object-cover"
                                />
                                <span
                                    class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-semibold text-primary-foreground"
                                >
                                    {{ line.quantity }}
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs font-medium">
                                    {{ line.product_name }}
                                </p>
                                <p
                                    class="mt-0.5 text-[10px] text-muted-foreground"
                                >
                                    Talle {{ line.size }} · {{ line.color }}
                                </p>
                            </div>
                            <p class="text-xs font-medium">
                                {{ formatMoney(line.total_cents) }}
                            </p>
                        </li>
                    </ul>

                    <div
                        class="mt-5 space-y-2.5 border-t border-border/70 pt-4 text-sm"
                    >
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatMoney(cart.subtotal_cents) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Envío</span>
                            <span
                                :class="
                                    shippingCost === 0
                                        ? 'font-medium text-emerald-700 dark:text-emerald-300'
                                        : ''
                                "
                            >
                                {{
                                    form.shipping_method === 'pickup'
                                        ? 'Retiro sin cargo'
                                        : formatMoney(shippingCost)
                                }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="mt-4 flex items-center justify-between border-t border-border/70 pt-4"
                    >
                        <span class="text-sm font-medium">Total</span>
                        <span class="font-serif text-3xl font-semibold">{{
                            formatMoney(total)
                        }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-5 flex h-12 w-full items-center justify-center gap-2 rounded-full bg-primary text-[11px] font-semibold tracking-[0.16em] text-primary-foreground uppercase transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Confirmando…'
                                : 'Confirmar pedido'
                        }}
                        <ArrowRight v-if="!form.processing" class="size-4" />
                    </button>

                    <p
                        class="mt-3 flex items-center justify-center gap-1.5 text-[10px] text-muted-foreground"
                    >
                        <LockKeyhole class="size-3" />
                        Tus datos están protegidos
                    </p>
                </div>
            </aside>
        </form>
    </div>
</template>

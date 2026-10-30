<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { discountPercent, formatMoney } from '@/lib/format';
import { show as productShow } from '@/routes/products';
import type { Product } from '@/types';

const props = defineProps<{
    product: Product;
    priority?: boolean;
}>();

const discount = computed(() =>
    discountPercent(
        props.product.price_cents,
        props.product.compare_at_price_cents,
    ),
);

const colors = computed(() => {
    const seen = new Set<string>();

    return (props.product.variants ?? [])
        .filter((variant) => {
            if (!variant.color_hex || seen.has(variant.color)) {
                return false;
            }

            seen.add(variant.color);

            return true;
        })
        .slice(0, 4);
});
</script>

<template>
    <Link :href="productShow(product.slug)" class="group block">
        <div
            class="relative aspect-[3/4] overflow-hidden rounded-xl bg-secondary"
        >
            <img
                v-if="product.primary_image_url"
                :src="product.primary_image_url"
                :alt="product.name"
                :loading="priority ? 'eager' : 'lazy'"
                class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.04]"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-accent via-secondary to-muted px-6 text-center"
            >
                <span class="font-serif text-2xl text-primary/60">
                    {{ product.name }}
                </span>
            </div>

            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                <span
                    v-if="discount"
                    class="rounded-full bg-primary px-2.5 py-1 text-[10px] font-semibold tracking-wide text-primary-foreground uppercase"
                >
                    -{{ discount }}%
                </span>
                <span
                    v-if="!product.in_stock"
                    class="rounded-full bg-foreground/85 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-background uppercase"
                >
                    Agotado
                </span>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <p class="eyebrow text-[10px]">
                {{ product.category?.name ?? 'Lunaria' }}
            </p>
            <h3
                class="font-serif text-lg leading-snug font-medium transition-colors group-hover:text-primary"
            >
                {{ product.name }}
            </h3>
            <div class="flex flex-wrap items-center gap-2 pt-0.5">
                <span class="text-sm font-semibold">
                    {{ formatMoney(product.price_cents) }}
                </span>
                <span
                    v-if="product.compare_at_price_cents"
                    class="text-xs text-muted-foreground line-through"
                >
                    {{ formatMoney(product.compare_at_price_cents) }}
                </span>
            </div>
            <div v-if="colors.length" class="flex items-center gap-1.5 pt-1">
                <span
                    v-for="color in colors"
                    :key="color.id"
                    class="size-2.5 rounded-full ring-1 ring-border ring-offset-1 ring-offset-background"
                    :style="{ backgroundColor: color.color_hex ?? undefined }"
                    :title="color.color"
                />
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, PackageCheck, RefreshCcw, ShieldCheck } from '@lucide/vue';
import ProductCard from '@/components/store/ProductCard.vue';
import { formatMoney } from '@/lib/format';
import { index as productsIndex } from '@/routes/products';
import { track as trackIndex } from '@/routes/orders';
import type { CategorySummary, Product } from '@/types';

const props = defineProps<{
    categories: CategorySummary[];
    featured: Product[];
    newArrivals: Product[];
}>();

const page = usePage();
const store = computed(() => page.props.store);

const heroImage = computed(() => {
    const vestidos = props.categories.find(
        (category) => category.slug === 'vestidos',
    );

    return (
        vestidos?.image_url ??
        props.featured[0]?.primary_image_url ??
        'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1800&q=85'
    );
});

const featuredProducts = computed(() => props.featured.slice(0, 4));
</script>

<template>
    <Head title="Lunaria — Ropa de autor" />

    <section class="relative overflow-hidden bg-foreground">
        <img
            :src="heroImage"
            alt="Nueva colección Lunaria"
            class="absolute inset-0 h-full w-full object-cover object-center opacity-90"
            fetchpriority="high"
        />
        <div
            class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/25 to-transparent"
        />
        <div
            class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-black/10"
        />

        <div
            class="store-container relative flex min-h-[70vh] items-end py-16 sm:min-h-[76vh] lg:min-h-[82vh]"
        >
            <div class="max-w-xl pb-4 text-white">
                <p
                    class="text-[10px] font-medium tracking-[0.32em] text-white/75 uppercase sm:text-[11px]"
                >
                    Nueva temporada · 2026
                </p>
                <h1
                    class="text-shadow-soft mt-4 font-serif text-5xl leading-[0.98] font-medium tracking-tight sm:text-6xl lg:text-7xl"
                >
                    Lo que se<br />siente tuyo.
                </h1>
                <p
                    class="mt-5 max-w-md text-sm leading-relaxed text-white/80 sm:text-base"
                >
                    Prendas femeninas de líneas limpias, materiales nobles y
                    producción local. Diseñadas para acompañarte todos los días.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <Link
                        :href="productsIndex()"
                        class="inline-flex h-12 items-center gap-2 rounded-full bg-white px-7 text-[12px] font-semibold tracking-[0.14em] text-[#231f1b] uppercase transition-colors hover:bg-white/90"
                    >
                        Ver colección
                        <ArrowRight class="size-4" />
                    </Link>
                    <Link
                        :href="trackIndex()"
                        class="inline-flex h-12 items-center rounded-full border border-white/35 px-6 text-[12px] font-medium tracking-[0.14em] text-white uppercase transition-colors hover:bg-white/10"
                    >
                        Seguí tu pedido
                    </Link>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-border/70 bg-card">
        <div class="store-container grid gap-6 py-7 sm:grid-cols-3">
            <div class="flex items-center gap-3">
                <PackageCheck class="size-5 text-primary" />
                <div>
                    <p class="text-xs font-semibold tracking-wide uppercase">
                        Envío gratis
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Desde
                        {{ formatMoney(store.free_shipping_from_cents) }} a todo
                        el país
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <RefreshCcw class="size-5 text-primary" />
                <div>
                    <p class="text-xs font-semibold tracking-wide uppercase">
                        Cambios sin cargo
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Tenés 15 días para cambiarlo
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <ShieldCheck class="size-5 text-primary" />
                <div>
                    <p class="text-xs font-semibold tracking-wide uppercase">
                        Compra protegida
                    </p>
                    <p class="text-xs text-muted-foreground">
                        MercadoPago y transferencia
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="categories.length" class="store-container py-16 lg:py-20">
        <div class="flex items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Explorá</p>
                <h2 class="mt-2 font-serif text-3xl font-medium sm:text-4xl">
                    Categorías
                </h2>
            </div>
            <Link
                :href="productsIndex()"
                class="link-underline hidden text-xs tracking-[0.16em] uppercase sm:inline-flex"
            >
                Ver todo
                <ArrowRight class="size-3.5" />
            </Link>
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                v-for="(category, index) in categories.slice(0, 5)"
                :key="category.id"
                :href="productsIndex({ query: { category: category.slug } })"
                :class="[
                    'group relative overflow-hidden rounded-xl bg-secondary',
                    index === 0
                        ? 'sm:col-span-2 lg:col-span-2 lg:row-span-2'
                        : '',
                ]"
            >
                <div
                    :class="[
                        'relative overflow-hidden',
                        index === 0
                            ? 'aspect-[16/10] lg:aspect-auto lg:h-full'
                            : 'aspect-[4/3] lg:aspect-[3/4]',
                    ]"
                >
                    <img
                        v-if="category.image_url"
                        :src="category.image_url"
                        :alt="category.name"
                        loading="lazy"
                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                    />
                    <div
                        v-else
                        class="h-full w-full bg-gradient-to-br from-accent to-secondary"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/5 to-transparent"
                    />
                    <div class="absolute inset-x-0 bottom-0 p-4">
                        <p
                            class="font-serif text-xl font-medium text-white sm:text-2xl"
                        >
                            {{ category.name }}
                        </p>
                        <p
                            class="mt-0.5 text-[10px] tracking-[0.16em] text-white/70 uppercase"
                        >
                            {{ category.products_count ?? 0 }} prendas
                        </p>
                    </div>
                </div>
            </Link>
        </div>
    </section>

    <section class="store-container pb-16 lg:pb-20">
        <div class="flex items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Selección Lunaria</p>
                <h2 class="mt-2 font-serif text-3xl font-medium sm:text-4xl">
                    Favoritos de la temporada
                </h2>
            </div>
            <Link
                :href="productsIndex()"
                class="link-underline hidden text-xs tracking-[0.16em] uppercase sm:inline-flex"
            >
                Ver toda la tienda
                <ArrowRight class="size-3.5" />
            </Link>
        </div>

        <div
            v-if="featuredProducts.length"
            class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4"
        >
            <ProductCard
                v-for="(product, index) in featuredProducts"
                :key="product.id"
                :product="product"
                :priority="index < 2"
            />
        </div>

        <div
            v-else
            class="mt-8 rounded-2xl border border-dashed border-border bg-card/50 px-6 py-14 text-center"
        >
            <p class="font-serif text-2xl">Estamos preparando la colección</p>
            <p class="mt-2 text-sm text-muted-foreground">
                Volvé pronto para ver las novedades.
            </p>
        </div>
    </section>

    <section class="bg-secondary/50 py-16 lg:py-20">
        <div class="store-container grid items-center gap-10 lg:grid-cols-2">
            <div class="order-2 lg:order-1">
                <p class="eyebrow">Nuestra mirada</p>
                <h2
                    class="mt-3 font-serif text-3xl leading-tight font-medium sm:text-4xl lg:text-5xl"
                >
                    Piezas que acompañan,<br />no que se imponen.
                </h2>
                <p
                    class="mt-5 max-w-lg text-sm leading-relaxed text-muted-foreground sm:text-base"
                >
                    Creemos en un guardarropa pequeño y consciente. Elegimos
                    textiles que se sienten bien en la piel y cortes que
                    favorecen sin esfuerzo. Cada prenda se produce en talleres
                    de Buenos Aires, en series cortas.
                </p>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="size-1.5 rounded-full bg-primary" />
                        Materiales naturales y proveedores locales
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="size-1.5 rounded-full bg-primary" />
                        Talles reales, calces pensados en cuerpos reales
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="size-1.5 rounded-full bg-primary" />
                        Producción en series cortas, sin sobrestock
                    </li>
                </ul>
                <Link
                    :href="productsIndex()"
                    class="mt-8 inline-flex h-11 items-center gap-2 rounded-full border border-primary px-6 text-[11px] font-semibold tracking-[0.16em] text-primary uppercase transition-colors hover:bg-primary hover:text-primary-foreground"
                >
                    Conocer la colección
                    <ArrowRight class="size-4" />
                </Link>
            </div>

            <div class="order-1 grid grid-cols-2 gap-3 lg:order-2">
                <div class="overflow-hidden rounded-xl">
                    <img
                        v-if="categories[1]?.image_url"
                        :src="categories[1].image_url"
                        :alt="categories[1].name"
                        loading="lazy"
                        class="aspect-[3/4] h-full w-full object-cover"
                    />
                </div>
                <div class="mt-8 overflow-hidden rounded-xl">
                    <img
                        v-if="
                            categories[3]?.image_url ??
                            newArrivals[0]?.primary_image_url
                        "
                        :src="
                            categories[3]?.image_url ??
                            newArrivals[0]?.primary_image_url ??
                            ''
                        "
                        :alt="categories[3]?.name ?? 'Lunaria'"
                        loading="lazy"
                        class="aspect-[3/4] h-full w-full object-cover"
                    />
                </div>
            </div>
        </div>
    </section>

    <section class="store-container py-16 lg:py-20">
        <div class="flex items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Recién llegado</p>
                <h2 class="mt-2 font-serif text-3xl font-medium sm:text-4xl">
                    Novedades
                </h2>
            </div>
            <Link
                :href="productsIndex({ query: { sort: 'recent' } })"
                class="link-underline hidden text-xs tracking-[0.16em] uppercase sm:inline-flex"
            >
                Ver novedades
                <ArrowRight class="size-3.5" />
            </Link>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">
            <ProductCard
                v-for="product in newArrivals"
                :key="product.id"
                :product="product"
            />
        </div>
    </section>

    <section class="store-container pb-20">
        <div
            class="relative overflow-hidden rounded-2xl bg-accent px-6 py-12 text-center sm:px-12 lg:py-16"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-16 size-56 rounded-full border border-primary/10"
            />
            <div
                class="pointer-events-none absolute -bottom-24 -left-16 size-72 rounded-full border border-primary/10"
            />
            <p class="eyebrow text-primary/70">Comunidad Lunaria</p>
            <h2 class="mt-3 font-serif text-3xl font-medium sm:text-4xl">
                Sumate a nuestro universo
            </h2>
            <p class="mx-auto mt-3 max-w-lg text-sm text-muted-foreground">
                Novedades, lanzamientos y contenido de estilo. Seguinos en
                Instagram y enterate primero de cada drop.
            </p>
            <a
                :href="`https://instagram.com/${store.contact.instagram}`"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-7 inline-flex h-11 items-center gap-2 rounded-full bg-primary px-6 text-[11px] font-semibold tracking-[0.16em] text-primary-foreground uppercase transition-colors hover:bg-primary/90"
            >
                Ir a Instagram
                <ArrowRight class="size-4" />
            </a>
        </div>
    </section>
</template>

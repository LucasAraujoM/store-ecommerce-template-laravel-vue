<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    AtSign,
    Mail,
    MapPin,
    Menu,
    Phone,
    Search,
    ShoppingBag,
    User,
    X,
} from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import { formatMoney } from '@/lib/format';
import { home, login, logout, register } from '@/routes';
import { index as accountOrdersIndex } from '@/routes/account/orders';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as cartIndex } from '@/routes/cart';
import { track as trackIndex } from '@/routes/orders';
import { index as productsIndex } from '@/routes/products';
import type { User as AuthUser } from '@/types';

const page = usePage();
const mobileOpen = ref(false);
const searchOpen = ref(false);
const searchTerm = ref('');

const user = computed(() => (page.props.auth.user ?? null) as AuthUser | null);
const isAdmin = computed(() =>
    Boolean(
        (user.value as (AuthUser & { is_admin?: boolean }) | null)?.is_admin,
    ),
);
const store = computed(() => page.props.store);
const categories = computed(() => page.props.categories);
const cartCount = computed(() => page.props.cartCount ?? 0);

function submitSearch(): void {
    const query = searchTerm.value.trim();

    if (query === '') {
        return;
    }

    router.get(productsIndex().url, { q: query });
    searchOpen.value = false;
    mobileOpen.value = false;
}

function closeMobile(): void {
    mobileOpen.value = false;
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <div class="bg-primary px-4 py-2 text-center text-primary-foreground">
            <p
                class="text-[10px] font-medium tracking-[0.22em] uppercase sm:text-[11px]"
            >
                Envío sin cargo desde
                {{ formatMoney(store.free_shipping_from_cents) }}
                <span class="hidden sm:inline">· 3 y 6 cuotas sin interés</span>
            </p>
        </div>

        <header
            class="sticky top-0 z-40 border-b border-border/70 bg-background/92 backdrop-blur-md"
        >
            <div class="store-container">
                <div
                    class="flex h-16 items-center justify-between gap-4 lg:h-20"
                >
                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-full text-foreground transition-colors hover:bg-secondary lg:hidden"
                        aria-label="Abrir menú"
                        @click="mobileOpen = true"
                    >
                        <Menu class="size-5" />
                    </button>

                    <Link
                        :href="home()"
                        class="group flex flex-col items-center lg:items-start"
                    >
                        <span
                            class="font-serif text-2xl leading-none font-semibold tracking-[0.3em] text-foreground transition-colors group-hover:text-primary lg:text-[26px]"
                        >
                            LUNARIA
                        </span>
                        <span
                            class="mt-1 hidden text-[9px] tracking-[0.32em] text-muted-foreground uppercase lg:block"
                        >
                            ropa de autor
                        </span>
                    </Link>

                    <nav class="hidden items-center gap-7 lg:flex">
                        <Link
                            :href="productsIndex()"
                            class="link-underline text-[12px] tracking-[0.16em] uppercase"
                        >
                            Tienda
                        </Link>
                        <Link
                            v-for="category in categories.slice(0, 3)"
                            :key="category.id"
                            :href="
                                productsIndex({
                                    query: { category: category.slug },
                                })
                            "
                            class="link-underline text-[12px] tracking-[0.16em] uppercase"
                        >
                            {{ category.name }}
                        </Link>
                        <Link
                            :href="trackIndex()"
                            class="link-underline text-[12px] tracking-[0.16em] uppercase"
                        >
                            Seguimiento
                        </Link>
                    </nav>

                    <div class="flex items-center gap-0.5 sm:gap-1">
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-secondary"
                            aria-label="Buscar"
                            @click="searchOpen = !searchOpen"
                        >
                            <Search class="size-[18px]" />
                        </button>

                        <Link
                            :href="user ? accountOrdersIndex() : login()"
                            class="flex size-9 items-center justify-center rounded-full transition-colors hover:bg-secondary"
                            :aria-label="user ? 'Mi cuenta' : 'Ingresar'"
                        >
                            <User class="size-[18px]" />
                        </Link>

                        <Link
                            :href="cartIndex()"
                            class="relative flex size-9 items-center justify-center rounded-full transition-colors hover:bg-secondary"
                            aria-label="Carrito"
                        >
                            <ShoppingBag class="size-[18px]" />
                            <span
                                v-if="cartCount > 0"
                                class="absolute -top-0.5 -right-0.5 flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-semibold text-primary-foreground"
                            >
                                {{ cartCount > 9 ? '9+' : cartCount }}
                            </span>
                        </Link>
                    </div>
                </div>

                <div v-if="searchOpen" class="border-t border-border/70 py-3">
                    <form
                        class="flex items-center gap-2"
                        @submit.prevent="submitSearch"
                    >
                        <Search class="size-4 shrink-0 text-muted-foreground" />
                        <input
                            v-model="searchTerm"
                            type="search"
                            placeholder="Buscar vestidos, blusas, accesorios..."
                            class="h-10 flex-1 border-0 bg-transparent text-sm outline-none placeholder:text-muted-foreground/60"
                            autofocus
                        />
                        <button
                            type="submit"
                            class="rounded-full bg-primary px-4 py-2 text-[11px] font-semibold tracking-wide text-primary-foreground uppercase"
                        >
                            Buscar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="mt-20 border-t border-border/70 bg-secondary/40">
            <div class="store-container py-14">
                <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-4">
                        <div>
                            <p
                                class="font-serif text-2xl font-semibold tracking-[0.3em]"
                            >
                                LUNARIA
                            </p>
                            <p
                                class="mt-1 text-[9px] tracking-[0.3em] text-muted-foreground uppercase"
                            >
                                ropa de autor
                            </p>
                        </div>
                        <p
                            class="max-w-xs text-sm leading-relaxed text-muted-foreground"
                        >
                            Prendas femeninas pensadas para durar, con
                            materiales nobles y producción local en Buenos
                            Aires.
                        </p>
                        <a
                            :href="`https://instagram.com/${store.contact.instagram}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 text-sm font-medium text-foreground transition-colors hover:text-primary"
                        >
                            <AtSign class="size-4" />
                            @{{ store.contact.instagram }}
                        </a>
                    </div>

                    <div>
                        <p class="eyebrow">Tienda</p>
                        <ul class="mt-4 space-y-2.5">
                            <li>
                                <Link
                                    :href="productsIndex()"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Ver todo
                                </Link>
                            </li>
                            <li
                                v-for="category in categories"
                                :key="category.id"
                            >
                                <Link
                                    :href="
                                        productsIndex({
                                            query: { category: category.slug },
                                        })
                                    "
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    {{ category.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="eyebrow">Ayuda</p>
                        <ul class="mt-4 space-y-2.5">
                            <li>
                                <Link
                                    :href="trackIndex()"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Seguí tu pedido
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="cartIndex()"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Mi carrito
                                </Link>
                            </li>
                            <li v-if="user">
                                <Link
                                    :href="accountOrdersIndex()"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Mis pedidos
                                </Link>
                            </li>
                            <li v-else>
                                <Link
                                    :href="login()"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Ingresar
                                </Link>
                            </li>
                            <li>
                                <a
                                    :href="`mailto:${store.contact.email}`"
                                    class="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Cambios y devoluciones
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <p class="eyebrow">Contacto</p>
                        <ul
                            class="mt-4 space-y-3 text-sm text-muted-foreground"
                        >
                            <li class="flex items-start gap-2">
                                <MapPin class="mt-0.5 size-4 shrink-0" />
                                <span>{{ store.contact.address }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <Mail class="size-4 shrink-0" />
                                <a
                                    :href="`mailto:${store.contact.email}`"
                                    class="transition-colors hover:text-foreground"
                                >
                                    {{ store.contact.email }}
                                </a>
                            </li>
                            <li class="flex items-center gap-2">
                                <Phone class="size-4 shrink-0" />
                                <span>{{ store.contact.phone }}</span>
                            </li>
                        </ul>
                        <div class="mt-5 flex flex-wrap gap-1.5">
                            <span
                                v-for="method in [
                                    'MercadoPago',
                                    'Transferencia',
                                    'Visa',
                                    'Mastercard',
                                ]"
                                :key="method"
                                class="rounded border border-border bg-card px-2 py-1 text-[10px] font-medium text-muted-foreground"
                            >
                                {{ method }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-border/70 pt-6 text-[11px] text-muted-foreground sm:flex-row"
                >
                    <p>
                        © {{ new Date().getFullYear() }} Lunaria. Todos los
                        derechos reservados.
                    </p>
                    <p class="flex items-center gap-2">
                        Hecho con amor en Buenos Aires
                        <template v-if="isAdmin">
                            <span class="text-primary">·</span>
                            <Link
                                :href="adminDashboard()"
                                class="transition-colors hover:text-foreground"
                            >
                                Panel admin
                            </Link>
                        </template>
                    </p>
                </div>
            </div>
        </footer>

        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
                <div
                    class="absolute inset-0 bg-foreground/30 backdrop-blur-sm"
                    @click="closeMobile"
                />
                <div
                    class="absolute inset-y-0 left-0 flex w-[86%] max-w-sm flex-col bg-background shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-border px-5 py-4"
                    >
                        <span class="font-serif text-xl tracking-[0.28em]"
                            >LUNARIA</span
                        >
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-full hover:bg-secondary"
                            aria-label="Cerrar menú"
                            @click="closeMobile"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-5 py-6">
                        <p class="eyebrow">Tienda</p>
                        <nav class="mt-3 flex flex-col">
                            <Link
                                :href="productsIndex()"
                                class="border-b border-border/60 py-3 text-lg font-medium"
                                @click="closeMobile"
                            >
                                Ver todo
                            </Link>
                            <Link
                                v-for="category in categories"
                                :key="category.id"
                                :href="
                                    productsIndex({
                                        query: { category: category.slug },
                                    })
                                "
                                class="border-b border-border/60 py-3 text-lg font-medium"
                                @click="closeMobile"
                            >
                                {{ category.name }}
                            </Link>
                        </nav>

                        <p class="eyebrow mt-8">Tu cuenta</p>
                        <nav class="mt-3 flex flex-col">
                            <template v-if="user">
                                <Link
                                    :href="accountOrdersIndex()"
                                    class="flex items-center justify-between border-b border-border/60 py-3 text-sm"
                                    @click="closeMobile"
                                >
                                    Mis pedidos
                                    <ArrowRight class="size-4" />
                                </Link>
                                <Link
                                    v-if="isAdmin"
                                    :href="adminDashboard()"
                                    class="flex items-center justify-between border-b border-border/60 py-3 text-sm"
                                    @click="closeMobile"
                                >
                                    Panel de administración
                                    <ArrowRight class="size-4" />
                                </Link>
                                <Link
                                    :href="logout()"
                                    method="post"
                                    as="button"
                                    class="flex items-center justify-between border-b border-border/60 py-3 text-left text-sm"
                                    @click="closeMobile"
                                >
                                    Cerrar sesión
                                    <ArrowRight class="size-4" />
                                </Link>
                            </template>
                            <template v-else>
                                <Link
                                    :href="login()"
                                    class="flex items-center justify-between border-b border-border/60 py-3 text-sm"
                                    @click="closeMobile"
                                >
                                    Ingresar
                                    <ArrowRight class="size-4" />
                                </Link>
                                <Link
                                    :href="register()"
                                    class="flex items-center justify-between border-b border-border/60 py-3 text-sm"
                                    @click="closeMobile"
                                >
                                    Crear cuenta
                                    <ArrowRight class="size-4" />
                                </Link>
                            </template>
                            <Link
                                :href="trackIndex()"
                                class="flex items-center justify-between border-b border-border/60 py-3 text-sm"
                                @click="closeMobile"
                            >
                                Seguí tu pedido
                                <ArrowRight class="size-4" />
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </Transition>

        <Toaster position="top-center" />
    </div>
</template>

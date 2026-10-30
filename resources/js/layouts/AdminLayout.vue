<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ExternalLink,
    LayoutDashboard,
    LogOut,
    Menu,
    Package,
    ShoppingBag,
    Tags,
    Truck,
    X,
} from '@lucide/vue';
import { Toaster } from '@/components/ui/sonner';
import { home, logout } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as adminCategoriesIndex } from '@/routes/admin/categories';
import { index as adminOrdersIndex } from '@/routes/admin/orders';
import { index as adminProductsIndex } from '@/routes/admin/products';
import { index as adminShippingZonesIndex } from '@/routes/admin/shipping-zones';
import type { User } from '@/types';

const page = usePage();
const mobileOpen = ref(false);

const user = computed(() => page.props.auth.user as User | null);

const navigation = computed(() => [
    {
        label: 'Dashboard',
        href: adminDashboard(),
        icon: LayoutDashboard,
        active: page.url === '/admin',
    },
    {
        label: 'Pedidos',
        href: adminOrdersIndex(),
        icon: ShoppingBag,
        active: page.url.startsWith('/admin/orders'),
    },
    {
        label: 'Productos',
        href: adminProductsIndex(),
        icon: Package,
        active: page.url.startsWith('/admin/products'),
    },
    {
        label: 'Categorías',
        href: adminCategoriesIndex(),
        icon: Tags,
        active: page.url.startsWith('/admin/categories'),
    },
    {
        label: 'Envíos',
        href: adminShippingZonesIndex(),
        icon: Truck,
        active: page.url.startsWith('/admin/shipping-zones'),
    },
]);
</script>

<template>
    <div class="min-h-screen bg-background lg:grid lg:grid-cols-[260px_1fr]">
        <aside
            class="hidden flex-col bg-primary text-primary-foreground lg:sticky lg:top-0 lg:flex lg:h-screen"
        >
            <div class="border-b border-white/10 px-6 py-6">
                <Link :href="adminDashboard()" class="block">
                    <p
                        class="font-serif text-xl font-semibold tracking-[0.3em]"
                    >
                        LUNARIA
                    </p>
                    <p
                        class="mt-1 text-[9px] tracking-[0.3em] text-primary-foreground/50 uppercase"
                    >
                        panel de gestión
                    </p>
                </Link>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                <Link
                    v-for="item in navigation"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors',
                        item.active
                            ? 'bg-white/12 font-medium text-white'
                            : 'text-primary-foreground/65 hover:bg-white/8 hover:text-white',
                    ]"
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.label }}
                </Link>
            </nav>

            <div class="border-t border-white/10 px-3 py-4">
                <Link
                    :href="home()"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-primary-foreground/65 transition-colors hover:bg-white/8 hover:text-white"
                >
                    <ExternalLink class="size-4" />
                    Ver tienda
                </Link>

                <div
                    class="mt-3 flex items-center gap-3 rounded-lg bg-white/5 px-3 py-2.5"
                >
                    <div
                        class="flex size-8 items-center justify-center rounded-full bg-white/10 text-xs font-semibold"
                    >
                        {{ user?.name?.charAt(0) ?? 'L' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium">
                            {{ user?.name }}
                        </p>
                        <p
                            class="truncate text-[10px] text-primary-foreground/50"
                        >
                            {{ user?.email }}
                        </p>
                    </div>
                    <Link
                        :href="logout()"
                        method="post"
                        as="button"
                        class="text-primary-foreground/50 transition-colors hover:text-white"
                        aria-label="Cerrar sesión"
                    >
                        <LogOut class="size-4" />
                    </Link>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-col">
            <header
                class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-border bg-background/92 px-4 backdrop-blur-md lg:hidden"
            >
                <button
                    type="button"
                    class="flex size-9 items-center justify-center rounded-full hover:bg-secondary"
                    aria-label="Abrir menú"
                    @click="mobileOpen = true"
                >
                    <Menu class="size-5" />
                </button>
                <Link
                    :href="adminDashboard()"
                    class="font-serif text-lg tracking-[0.3em]"
                >
                    LUNARIA
                </Link>
                <Link
                    :href="home()"
                    class="flex size-9 items-center justify-center rounded-full hover:bg-secondary"
                    aria-label="Ver tienda"
                >
                    <ExternalLink class="size-4" />
                </Link>
            </header>

            <main class="flex-1">
                <slot />
            </main>
        </div>

        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
                <div
                    class="absolute inset-0 bg-foreground/30 backdrop-blur-sm"
                    @click="mobileOpen = false"
                />
                <div
                    class="absolute inset-y-0 left-0 flex w-[84%] max-w-xs flex-col bg-primary text-primary-foreground shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-white/10 px-5 py-4"
                    >
                        <span class="font-serif text-lg tracking-[0.3em]"
                            >LUNARIA</span
                        >
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-full hover:bg-white/10"
                            aria-label="Cerrar menú"
                            @click="mobileOpen = false"
                        >
                            <X class="size-5" />
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 px-3 py-5">
                        <Link
                            v-for="item in navigation"
                            :key="item.label"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-3 rounded-lg px-3 py-3 text-sm',
                                item.active
                                    ? 'bg-white/12 font-medium text-white'
                                    : 'text-primary-foreground/65',
                            ]"
                            @click="mobileOpen = false"
                        >
                            <component :is="item.icon" class="size-4" />
                            {{ item.label }}
                        </Link>
                    </nav>

                    <div class="border-t border-white/10 px-3 py-4">
                        <Link
                            :href="home()"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-primary-foreground/65"
                            @click="mobileOpen = false"
                        >
                            <ExternalLink class="size-4" />
                            Ver tienda
                        </Link>
                        <Link
                            :href="logout()"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-primary-foreground/65"
                        >
                            <LogOut class="size-4" />
                            Cerrar sesión
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>

        <Toaster position="top-center" />
    </div>
</template>

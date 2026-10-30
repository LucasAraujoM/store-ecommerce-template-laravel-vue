<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-svh bg-background lg:grid-cols-[1.05fr_0.95fr]">
        <div class="relative hidden overflow-hidden bg-foreground lg:block">
            <img
                src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1200&q=85"
                alt="Lunaria"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-black/10"
            />

            <div
                class="relative flex h-full flex-col justify-between p-10 xl:p-14"
            >
                <Link :href="home()" class="flex flex-col">
                    <span
                        class="font-serif text-2xl font-semibold tracking-[0.32em] text-white"
                    >
                        LUNARIA
                    </span>
                    <span
                        class="mt-1 text-[9px] tracking-[0.34em] text-white/60 uppercase"
                    >
                        ropa de autor
                    </span>
                </Link>

                <div class="max-w-md text-white">
                    <p
                        class="text-[10px] font-medium tracking-[0.28em] text-white/65 uppercase"
                    >
                        Buenos Aires · Argentina
                    </p>
                    <p
                        class="mt-4 font-serif text-3xl leading-tight font-medium xl:text-4xl"
                    >
                        “Vestirte debería sentirse como volver a casa.”
                    </p>
                    <p class="mt-4 text-sm leading-relaxed text-white/70">
                        Prendas de producción local, series cortas y materiales
                        que acompañan el cuerpo.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col p-6 sm:p-10">
            <div class="flex items-center justify-between">
                <Link :href="home()" class="flex flex-col lg:hidden">
                    <span
                        class="font-serif text-xl font-semibold tracking-[0.3em]"
                        >LUNARIA</span
                    >
                </Link>

                <Link
                    :href="home()"
                    class="ml-auto inline-flex items-center gap-1.5 text-[11px] font-medium tracking-[0.14em] text-muted-foreground uppercase transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="size-3.5" />
                    Volver a la tienda
                </Link>
            </div>

            <div
                class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center py-10"
            >
                <p class="eyebrow">Tu cuenta</p>
                <h1 class="mt-3 font-serif text-4xl leading-tight font-medium">
                    {{ title }}
                </h1>
                <p
                    v-if="description"
                    class="mt-3 text-sm leading-relaxed text-muted-foreground"
                >
                    {{ description }}
                </p>

                <div class="mt-8">
                    <slot />
                </div>
            </div>

            <p class="text-center text-[10px] text-muted-foreground">
                © {{ new Date().getFullYear() }} Lunaria · Hecho con amor en
                Buenos Aires
            </p>
        </div>
    </div>
</template>

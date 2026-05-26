<script setup lang="ts">
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="min-h-svh bg-evo-white text-evo-black"
    >
        <div class="grid min-h-svh lg:grid-cols-[minmax(0,0.9fr)_minmax(420px,560px)]">
            <section class="relative hidden overflow-hidden bg-evo-black text-evo-white lg:block">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_25%_20%,rgba(255,129,62,0.28),transparent_28%),radial-gradient(circle_at_80%_75%,rgba(122,72,150,0.32),transparent_30%)]" />
                <div class="relative flex h-full flex-col justify-between p-10">
                    <div class="font-michroma text-sm uppercase tracking-[0.18em] text-evo-white/70">
                        Evolyx
                    </div>
                    <div class="max-w-lg space-y-4 pb-4">
                        <p class="text-sm font-medium uppercase tracking-[0.16em] text-evo-orange">
                            Performance tracking
                        </p>
                        <h2 class="text-5xl font-bold leading-tight">
                            Construis ton profil, suis tes progrès.
                        </h2>
                        <p class="max-w-md text-base leading-7 text-evo-white/68">
                            Entraînement, nutrition et évolution physique réunis dans un espace clair.
                        </p>
                    </div>
                </div>
            </section>

            <main class="flex min-h-svh items-center justify-center p-4">
                <div class="w-full max-w-2xl">
                    <div class="rounded-xl border border-evo-black/10 bg-evo-white/78 p-4 shadow-[0_24px_80px_rgba(4,3,5,0.10)] backdrop-blur">
                        <div class="mb-4 space-y-2">
                            <p class="font-michroma text-xs uppercase tracking-[0.18em] text-evo-purple">
                                Evolyx
                            </p>
                            <h1 class="text-3xl font-bold leading-tight">{{ title }}</h1>
                            <p class="text-sm leading-6 text-evo-black/58">
                                {{ description }}
                            </p>
                        </div>
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

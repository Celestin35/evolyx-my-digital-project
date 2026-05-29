<script setup lang="ts">
withDefaults(
    defineProps<{
        locked: boolean;
        featureName: string;
        currentPlan?: string | null;
        description?: string;
        plainWhenUnlocked?: boolean;
    }>(),
    {
        currentPlan: null,
        description: 'Accessible avec un abonnement Premium.',
        plainWhenUnlocked: false,
    },
);
</script>

<template>
    <slot v-if="!locked && plainWhenUnlocked" />

    <section v-else class="relative rounded-lg bg-evo-white p-4">
        <slot v-if="!locked && !plainWhenUnlocked" />
        <slot v-else name="locked-preview" />

        <div
            v-if="locked"
            class="absolute inset-0 flex items-center justify-center rounded-lg bg-evo-white/80 p-4 text-center"
        >
            <div
                class="rounded-lg border border-neutral-200 bg-evo-white p-4 shadow-sm"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-neutral-500 uppercase"
                >
                    Fonction Premium
                </p>
                <h2 class="mt-2 text-lg font-semibold text-evo-black">
                    {{ featureName }}
                </h2>
                <p class="mt-2 text-sm text-neutral-700">
                    {{ description }}
                </p>
                <p class="mt-1 text-xs text-neutral-500">
                    Offre actuelle: {{ currentPlan ?? 'Aucune' }}
                </p>
            </div>
        </div>
    </section>
</template>

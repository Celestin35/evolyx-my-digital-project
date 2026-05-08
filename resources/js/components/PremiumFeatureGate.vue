<script setup lang="ts">
withDefaults(
    defineProps<{
        locked: boolean;
        featureName: string;
        currentPlan?: string | null;
        description?: string;
    }>(),
    {
        currentPlan: null,
        description: 'Accessible avec un abonnement Premium.',
    },
);
</script>

<template>
    <section class="relative rounded-lg bg-white p-4">
        <slot v-if="!locked" />
        <slot v-else name="locked-preview" />

        <div
            v-if="locked"
            class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/80 p-4 text-center"
        >
            <div class="rounded-lg border border-neutral-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">
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

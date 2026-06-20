<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { profile } from '@/routes';

defineProps<{
    targetCalories: number | null;
    consumedCalories: number;
    remainingCalories: number;
}>();
</script>

<template>
    <section class="rounded-lg bg-evo-white p-4">
        <div
            class="flex h-full flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="flex h-full flex-col justify-between">
                <p
                    class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                >
                    Objectif du jour
                </p>
                <div>
                    <p
                        class="mt-5 text-6xl leading-none font-semibold text-evo-black"
                    >
                        {{ targetCalories }}
                    </p>
                    <p class="mt-2 text-base text-neutral-600">
                        kcal à consommer aujourd'hui
                    </p>
                </div>
            </div>

            <div
                class="flex h-full max-w-md flex-col justify-between space-y-4 sm:text-right"
            >
                <div class="w-fit sm:self-end">
                    <Button :as="Link" :href="profile()">
                        Modifier mon objectif
                    </Button>
                </div>

                <div
                    class="mt-5 rounded-lg border border-neutral-300 bg-evo-gray px-4 py-3 shadow-sm"
                >
                    <p class="text-base text-evo-black">
                        {{ consumedCalories }} kcal consommées
                    </p>
                    <p class="text-base text-evo-black">
                        {{ remainingCalories }} kcal restantes
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

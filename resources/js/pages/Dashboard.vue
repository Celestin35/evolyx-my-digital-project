<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import WeightChart from '@/components/WeightChart.vue';

type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

defineProps<{
    weightEntries: WeightEntry[];
}>();
</script>

<template>
    <Head title="Tableau de bord" />

    <AppLayout
        title="Tableau de bord"
        subtitle="Bienvenue sur votre tableau de bord personnel !"
    >
        <section
            class="grid h-full min-h-0 grid-cols-1 gap-4 lg:grid-cols-2 lg:grid-rows-2"
        >
            <div class="flex min-h-0 h-full flex-col rounded-lg bg-white p-4">
                <h2 class="mb-4 text-xl font-bold">Suivi du poids</h2>
                <div class="min-h-0 flex-1">
                    <WeightChart :weight-entries="weightEntries" />
                </div>
            </div>
        </section>
    </AppLayout>
</template>

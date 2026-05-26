<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: "Paramètres d'apparence",
        href: edit(),
    },
];
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbItems"
        title="Paramètres d'apparence"
        subtitle="Personnalisez l'apparence generale de votre compte."
    >
        <Head title="Paramètres d'apparence" />

        <h1 class="sr-only">Paramètres d'apparence</h1>

        <SettingsLayout>
            <div class="rounded-lg bg-evo-white p-4 dark:bg-neutral-900">
                <div class="space-y-4">
                    <Heading
                        variant="small"
                        title="Apparence"
                        description="Personnalisez l'apparence generale de votre compte."
                    />
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

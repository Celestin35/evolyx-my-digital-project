<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <section class="space-y-3">
            <h1 class="text-3xl font-semibold tracking-tight">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Base propre pour construire le front.
            </p>
        </section>
    </AppLayout>
</template>

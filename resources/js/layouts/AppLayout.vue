<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <main class="mx-auto flex w-full max-w-7xl flex-col px-6 py-8">
            <slot />
        </main>
    </div>
</template>

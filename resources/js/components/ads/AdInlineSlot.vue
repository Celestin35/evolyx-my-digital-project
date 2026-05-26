<script setup lang="ts">
import { BadgePercent } from 'lucide-vue-next';

defineProps<{
    enabled: boolean;
    variant?: 'banner' | 'square';
}>();
</script>

<template>
    <aside
        v-if="enabled"
        class="rounded-lg border border-evo-purple/25 bg-evo-white p-4 text-evo-black shadow-sm dark:border-evo-purple/45 dark:bg-neutral-900 dark:text-evo-white"
        :class="variant === 'square' ? 'min-h-80' : 'min-h-[120px]'"
        aria-label="Publicite"
    >
        <div
            class="flex items-center justify-center"
            :class="variant === 'square' ? 'min-h-72' : 'min-h-[88px]'"
        >
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-evo-orange/15 text-evo-orange"
                >
                    <BadgePercent class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <p class="text-base font-semibold">Espace publicitaire</p>
                </div>
            </div>
        </div>
    </aside>
</template>

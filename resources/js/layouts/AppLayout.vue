<script setup lang="ts">
import AdPopup from '@/components/ads/AdPopup.vue';
import DashboardHeader from '@/components/layout/DashboardHeader.vue';
import DashboardSidebar from '@/components/layout/DashboardSidebar.vue';
import MobileHeader from '@/components/layout/MobileHeader.vue';
import MobileNavigation from '@/components/layout/MobileNavigation.vue';
import { useAds } from '@/composables/useAds';

defineProps<{
    title: string;
    subtitle?: string;
}>();

const ads = useAds();
</script>

<template>
    <div
        class="fixed inset-0 flex overflow-hidden bg-gray-200 pt-4 px-4 text-evo-black transition-colors dark:bg-neutral-950 dark:text-evo-white lg:p-4"
    >
        <DashboardSidebar />
        <MobileNavigation />

        <div
            class="flex min-h-0 min-w-0 flex-1 flex-col gap-4 overflow-x-hidden overflow-y-auto [scrollbar-gutter:stable] lg:ml-[calc(20%+1.5rem)] lg:pr-4"
        >
            <MobileHeader />
            <DashboardHeader :title="title" :subtitle="subtitle" />
            <main class="min-h-0 flex-1">
                <slot />
            </main>
        </div>

        <AdPopup
            :enabled="ads.enabled"
            :interval-minutes="ads.popup_interval_minutes"
            :close-delay-seconds="ads.close_delay_seconds"
        />
    </div>
</template>

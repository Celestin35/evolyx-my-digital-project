<script setup lang="ts">
import DashboardHeader from '@/components/layout/DashboardHeader.vue'
import DashboardSidebar from '@/components/layout/DashboardSidebar.vue'

defineProps<{
  title: string,
  subtitle?: string
}>()

</script>

<template>
  <div class="fixed inset-0 flex overflow-hidden bg-gray-200 p-4">
    <DashboardSidebar />

    <div class="flex min-h-0 min-w-0 flex-1 flex-col gap-4 overflow-y-auto overflow-x-hidden pr-4 [scrollbar-gutter:stable] lg:ml-[calc(20%+1.5rem)]">
      <DashboardHeader :title="title" :subtitle="subtitle" />
      <main class="min-h-0 flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>

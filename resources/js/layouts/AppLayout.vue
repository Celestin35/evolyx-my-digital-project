<script setup lang="ts">
import DashboardHeader from '@/components/layout/DashboardHeader.vue'
import DashboardSidebar from '@/components/layout/DashboardSidebar.vue'

defineProps<{
  title: string,
  subtitle?: string
}>()

</script>

<template>
  <div class="flex min-h-dvh w-full overflow-hidden bg-gray-200 p-4">
    <DashboardSidebar />

    <div class="flex min-h-0 min-w-0 flex-1 flex-col gap-4 overflow-hidden lg:ml-[calc(20%+0.6rem)]">
      <DashboardHeader :title="title" :subtitle="subtitle" />
      <main class="min-h-0 flex-1 overflow-hidden">
        <slot />
      </main>
    </div>
  </div>
</template>

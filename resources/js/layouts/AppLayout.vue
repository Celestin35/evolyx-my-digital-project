<script setup lang="ts">
import DashboardHeader from '@/components/layout/DashboardHeader.vue'
import DashboardSidebar from '@/components/layout/DashboardSidebar.vue'

defineProps<{
  title: string,
  subtitle?: string
}>()

</script>

<template>
  <div class="flex min-h-dvh min-w-dvw  bg-gray-200 p-4">
    <DashboardSidebar />

    <div class="ml-4 flex min-w-0 flex-1 flex-col gap-4">
      <DashboardHeader :title="title" :subtitle="subtitle" />
      <main class="flex-1">
        <slot />
      </main>
    </div>
  </div>
</template>

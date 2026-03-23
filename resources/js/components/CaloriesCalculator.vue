<script setup lang="ts">
import { calculateBMR, calculateTDEE } from '@/lib/calories-macros'
import { onMounted, ref } from 'vue'

const mockData = {
    sex: 'male' as const,
    age: 20,
    weightKg: 89,
    heightCm: 175,
    activityLevel: 'light' as const,
    goal: 'maintain' as const,
}

const bmr = ref<number | null>(null)
const tdee = ref<number | null>(null)

function calculate() {
    const nextBmr = calculateBMR({
        sex: mockData.sex,
        age: mockData.age,
        weightKg: mockData.weightKg,
        heightCm: mockData.heightCm,
    })

    bmr.value = nextBmr
    tdee.value = calculateTDEE(nextBmr, mockData.activityLevel)
}
</script>

<template>
    <section class="space-y-3">
        <h2 class="text-lg font-semibold">Calories Calculator</h2>
        <pre class="rounded-md bg-muted p-3 text-sm">BMR: {{ bmr }}</pre>
        <pre class="rounded-md bg-muted p-3 text-sm">TDEE: {{ tdee }}</pre>
        <button
            class="rounded-md bg-black border border-white px-4 py-2 text-sm text-white hover:bg-white hover:text-black hover:cursor-pointer transition-colors duration-200"
            @click="calculate"
        >Calculate</button>
    </section>
</template>
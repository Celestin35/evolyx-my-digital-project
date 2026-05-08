<script setup lang="ts">
import { onMounted, ref } from 'vue'

type CaloriesResponse = {
    user_data: {
        age: number | null
        weight: number | string | null
        height: number | null
        sex: string | null
        activity_level: string | null
    }
    calculation: {
        bmr: number
        maintenance_calories: number
        target_calories: number
        macros: {
            protein: number
            fats: number
            carbs: number
        }
    }
}

const error = ref<string | null>(null)
const result = ref<CaloriesResponse | null>(null)

async function loadCalories() {
    error.value = null

    try {
        const response = await fetch('/calories/data', {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        })

        const data = await response.json()

        if (!response.ok) {
            throw new Error(data.message ?? 'Impossible de calculer les calories.')
        }

        result.value = data
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Une erreur est survenue.'
    }
}

</script>

<template>
    <section class="space-y-4">
        <h2 class="text-lg font-semibold">Calories Calculator</h2>
        <button
            class="rounded-md bg-black border border-white px-4 py-2 text-sm text-white hover:bg-white hover:text-black hover:cursor-pointer transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-60"
            @click="loadCalories"
        >
            Calculer mes calories
            </button>
        <p
            v-if="error"
            class="rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </p>
        <div
            v-else-if="result"
            class="space-y-3"
        >
            <pre class="rounded-md bg-muted p-3 text-sm">Poids actuel: {{ result.user_data.weight }} kg</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Âge: {{ result.user_data.age }} ans</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">BMR: {{ result.calculation.bmr }}</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Maintenance: {{ result.calculation.maintenance_calories }}</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Cible: {{ result.calculation.target_calories }}</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Protéines: {{ result.calculation.macros.protein }} g</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Lipides: {{ result.calculation.macros.fats }} g</pre>
            <pre class="rounded-md bg-muted p-3 text-sm">Glucides: {{ result.calculation.macros.carbs }} g</pre>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    form: {
        weight: string;
        body_fat: string;
        entry_date: string;
        processing: boolean;
        recentlySuccessful: boolean;
        errors: {
            weight?: string;
            body_fat?: string;
            entry_date?: string;
        };
    };
    todayDate: string;
    flashSuccessMessage?: string | null;
}>();

const emit = defineEmits<{
    submit: [];
}>();
</script>

<template>
    <section class="rounded-lg bg-evo-white p-4">
        <h2 class="text-lg font-semibold">Ajouter une entrée de poids</h2>

        <p class="mt-2 text-sm text-neutral-600">
            La masse grasse est optionnelle.
        </p>

        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div class="space-y-2">
                <label for="entry_weight" class="block font-medium">
                    Poids (kg)
                </label>
                <input
                    id="entry_weight"
                    v-model="form.weight"
                    type="number"
                    min="20"
                    max="500"
                    step="0.01"
                    class="evo-input"
                />
                <p v-if="form.errors.weight" class="text-sm text-red-600">
                    {{ form.errors.weight }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="entry_date" class="block font-medium">
                    Date de mesure
                </label>
                <input
                    id="entry_date"
                    v-model="form.entry_date"
                    type="date"
                    :max="todayDate"
                    class="evo-input"
                />
                <p v-if="form.errors.entry_date" class="text-sm text-red-600">
                    {{ form.errors.entry_date }}
                </p>
            </div>

            <div class="space-y-2">
                <label for="entry_body_fat" class="block font-medium">
                    Masse grasse (%)
                </label>
                <input
                    id="entry_body_fat"
                    v-model="form.body_fat"
                    type="number"
                    min="2"
                    max="75"
                    step="0.01"
                    class="evo-input"
                />
                <p v-if="form.errors.body_fat" class="text-sm text-red-600">
                    {{ form.errors.body_fat }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-4">
            <Button
                type="button"
                :disabled="form.processing"
                @click="emit('submit')"
            >
                {{ form.processing ? 'Enregistrement...' : "Ajouter l'entrée" }}
            </Button>
            <p
                v-if="form.recentlySuccessful || flashSuccessMessage"
                class="text-sm text-emerald-700"
            >
                {{ flashSuccessMessage ?? 'Entrée enregistrée.' }}
            </p>
        </div>
    </section>
</template>

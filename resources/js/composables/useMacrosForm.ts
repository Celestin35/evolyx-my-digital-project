import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { CaloriesOverview, MacrosField } from '@/types/nutrition';

export function useMacrosForm(caloriesOverview: CaloriesOverview) {
    const isEditingMacros = ref(false);
    const macrosForm = useForm({
        protein: caloriesOverview.macros?.protein ?? 0,
        carbs: caloriesOverview.macros?.carbs ?? 0,
        fats: caloriesOverview.macros?.fats ?? 0,
    });

    const baseTargetCalories = computed(
        () => caloriesOverview.target_calories ?? 0,
    );
    const editedCalories = computed(() => {
        const protein = Number(macrosForm.protein) || 0;
        const carbs = Number(macrosForm.carbs) || 0;
        const fats = Number(macrosForm.fats) || 0;

        return protein * 4 + carbs * 4 + fats * 9;
    });
    const caloriesDelta = computed(
        () => editedCalories.value - baseTargetCalories.value,
    );
    const isCaloriesOverBase = computed(
        () => editedCalories.value > baseTargetCalories.value,
    );

    const resetMacrosDefaults = () => {
        macrosForm.defaults({
            protein: caloriesOverview.macros?.protein ?? 0,
            carbs: caloriesOverview.macros?.carbs ?? 0,
            fats: caloriesOverview.macros?.fats ?? 0,
        });
        macrosForm.reset();
        macrosForm.clearErrors();
    };

    const startMacrosEdit = () => {
        resetMacrosDefaults();
        isEditingMacros.value = true;
    };

    const cancelMacrosEdit = () => {
        macrosForm.reset();
        macrosForm.clearErrors();
        isEditingMacros.value = false;
    };

    const stepMacro = (field: MacrosField, delta: number) => {
        const currentValue = Number(macrosForm[field]) || 0;
        macrosForm[field] = Math.max(0, currentValue + delta);
    };

    const saveMacros = () => {
        macrosForm.patch('/nutrition/macros', {
            preserveScroll: true,
            onSuccess: () => {
                isEditingMacros.value = false;
            },
        });
    };

    return {
        isEditingMacros,
        macrosForm,
        editedCalories,
        caloriesDelta,
        isCaloriesOverBase,
        startMacrosEdit,
        cancelMacrosEdit,
        stepMacro,
        saveMacros,
    };
}

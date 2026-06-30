export type CaloriesOverview = {
    current_weight: string | number | null;
    target_calories: number | null;
    consumed_calories: number | null;
    macros: {
        protein: number;
        fats: number;
        carbs: number;
    } | null;
};

export type MacrosField = 'protein' | 'carbs' | 'fats';

export const WEIGHT_STEP = 0.25;

export const weeklyGoalOptions = [
    { value: -1, label: 'Perdre 1 kg par semaine' },
    { value: -0.75, label: 'Perdre 0.75 kg par semaine' },
    { value: -0.5, label: 'Perdre 0.5 kg par semaine' },
    { value: -0.25, label: 'Perdre 0.25 kg par semaine' },
    { value: 0, label: 'Maintenir mon poids' },
    { value: 0.25, label: 'Gagner 0.25 kg par semaine' },
    { value: 0.5, label: 'Gagner 0.5 kg par semaine' },
    { value: 0.75, label: 'Gagner 0.75 kg par semaine' },
    { value: 1, label: 'Gagner 1 kg par semaine' },
] as const;

export const sexOptions = [
    { value: 'male', label: 'Homme' },
    { value: 'female', label: 'Femme' },
    { value: 'other', label: 'Autre' },
] as const;

export const activityLevelOptions = [
    { value: 'sedentary', label: 'Sedentaire' },
    { value: 'light', label: 'Leger' },
    { value: 'moderate', label: 'Modere' },
    { value: 'active', label: 'Actif' },
    { value: 'very_active', label: 'Tres actif' },
] as const;

export const parseWeight = (value: string | number | null): number | null => {
    if (value === null || value === '') {
        return null;
    }

    const parsedValue =
        typeof value === 'number' ? value : Number.parseFloat(value);

    if (Number.isNaN(parsedValue)) {
        return null;
    }

    return parsedValue;
};

export const roundToQuarter = (value: number): number =>
    Math.round(value / WEIGHT_STEP) * WEIGHT_STEP;

export const formatFrenchDate = (value: string | Date | null): string | null => {
    if (!value) {
        return null;
    }

    const date = value instanceof Date ? value : new Date(value);

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(date);
};

export const formatSexLabel = (value: string | null): string | null => {
    return (
        {
            male: 'Homme',
            female: 'Femme',
            other: 'Autre',
        }[value ?? ''] ?? value
    );
};

export const formatActivityLevelLabel = (value: string | null): string | null => {
    return (
        {
            sedentary: 'Sedentaire',
            light: 'Leger',
            moderate: 'Modere',
            active: 'Actif',
            very_active: 'Tres actif',
        }[value ?? ''] ?? value
    );
};

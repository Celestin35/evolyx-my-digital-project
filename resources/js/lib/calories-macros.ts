
// Formule BMR (Mifflin-St Jeor)
// Homme: BMR = 10*poids(kg) + 6.25*taille(cm) - 5*âge + 5
// Femme: BMR = 10*poids(kg) + 6.25*taille(cm) - 5*âge - 161

export const ACTIVITY_FACTORS = {
    sedentary: 1.2, // Peu ou pas d'exercice
    light: 1.375, // 1-3 jours/semaine
    moderate: 1.55, // 3-5 jours/semaine
    active: 1.725, // 6-7 jours/semaine
    veryActive: 1.9, // Travail physique + sport intense
} as const

export function calculateBMR({ sex, age, weightKg, heightCm }: { sex: 'male' | 'female', age: number, weightKg: number, heightCm: number }) {
    if (sex === 'male') {
        return 10 * weightKg + 6.25 * heightCm - 5 * age + 5;
    }
    return 10 * weightKg + 6.25 * heightCm - 5 * age - 161;
}

export function calculateTDEE(bmr: number, activityLevel: keyof typeof ACTIVITY_FACTORS) {
    return bmr * ACTIVITY_FACTORS[activityLevel];
}
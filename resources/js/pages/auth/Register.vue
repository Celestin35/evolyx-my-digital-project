<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, ChevronRight, Dumbbell, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

type SportOption = {
    id: number;
    name: string;
};

defineProps<{
    availableSports: SportOption[];
}>();

const currentStep = ref<'account' | 'profile'>('account');
const validatingAccount = ref(false);
const accountValidationError = ref('');

const form = useForm({
    first_name: '',
    pseudo: '',
    email: '',
    password: '',
    password_confirmation: '',
    sex: '',
    height: '',
    weight: '',
    activity_level: 'moderate',
    birth_date: '',
    sport_ids: [] as number[],
});

const activityLevelOptions = [
    { value: 'sedentary', label: 'Sédentaire (travail assis, peu ou pas de sport)' },
    { value: 'light', label: 'Léger (1 à 2 séances de sport par semaine)' },
    { value: 'moderate', label: 'Modéré (3 à 4 séances de sport par semaine)' },
    { value: 'active', label: 'Actif (5 à 6 séances de sport par semaine)' },
    { value: 'very_active', label: 'Très actif (sport quotidien ou travail physique)' },
];

const selectedSportsCount = computed(() => form.sport_ids.length);
const todayDate = computed(() => formatDateInput(new Date()));
const minimumBirthDate = computed(() => {
    const date = new Date();

    date.setFullYear(date.getFullYear() - 15);

    return formatDateInput(date);
});

function formatDateInput(date: Date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function validateAccountFields() {
    form.clearErrors('first_name', 'pseudo', 'email', 'password', 'password_confirmation');
    accountValidationError.value = '';

    if (!form.first_name.trim()) {
        form.setError('first_name', 'Le nom est requis.');
    }

    if (!form.pseudo.trim()) {
        form.setError('pseudo', 'Le pseudo est requis.');
    } else if (form.pseudo.length < 3 || form.pseudo.length > 30 || !/^[A-Za-z0-9_]+$/.test(form.pseudo)) {
        form.setError('pseudo', 'Le pseudo doit faire 3 à 30 caractères et ne contenir que des lettres, chiffres et underscores.');
    }

    if (!form.email.trim()) {
        form.setError('email', "L'email est requis.");
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        form.setError('email', "L'email doit être une adresse valide.");
    }

    if (
        form.password.length < 12
        || !/[a-z]/.test(form.password)
        || !/[A-Z]/.test(form.password)
        || !/\d/.test(form.password)
        || !/[^A-Za-z0-9]/.test(form.password)
    ) {
        form.setError('password', 'Le mot de passe doit contenir au moins 12 caractères, une majuscule, une minuscule, un chiffre et un symbole.');
    }

    if (form.password !== form.password_confirmation) {
        form.setError('password_confirmation', 'Les mots de passe ne correspondent pas.');
    }

    if (
        form.errors.first_name
        || form.errors.pseudo
        || form.errors.email
        || form.errors.password
        || form.errors.password_confirmation
    ) {
        return false;
    }

    return true;
}

async function goToProfile() {
    if (!validateAccountFields()) {
        return;
    }

    validatingAccount.value = true;
    accountValidationError.value = '';

    try {
        const response = await fetch('/register/validate-account', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                first_name: form.first_name,
                pseudo: form.pseudo,
                email: form.email,
                password: form.password,
                password_confirmation: form.password_confirmation,
            }),
        });

        if (response.status === 422) {
            const data = await response.json();

            Object.entries(data.errors ?? {}).forEach(([field, messages]) => {
                const [message] = messages as string[];

                form.setError(field as keyof typeof form.errors, message);
            });

            return;
        }

        if (!response.ok) {
            if (response.status === 419) {
                accountValidationError.value = 'La session a expiré. Recharge la page puis réessaie.';
            } else if (response.status === 403) {
                accountValidationError.value = 'Tu es déjà connecté. Déconnecte-toi pour créer un autre compte.';
            } else if (response.status === 404) {
                accountValidationError.value = "La vérification du compte n'est pas disponible sur le serveur. Vérifie que la dernière version est bien déployée.";
            } else {
                accountValidationError.value = 'Une erreur serveur empêche la vérification du compte. Réessaie dans un instant.';
            }

            return;
        }

        form.clearErrors('first_name', 'pseudo', 'email', 'password', 'password_confirmation');
    } catch {
        accountValidationError.value = 'Impossible de joindre le serveur. Vérifie ta connexion puis réessaie.';
    } finally {
        validatingAccount.value = false;
    }

    if (accountValidationError.value) {
        return;
    }

    currentStep.value = 'profile';
}

function goToAccount() {
    currentStep.value = 'account';
}

function toggleSport(sportId: number) {
    form.sport_ids = form.sport_ids.includes(sportId)
        ? form.sport_ids.filter((id) => id !== sportId)
        : [...form.sport_ids, sportId];
}

function validateProfileFields() {
    form.clearErrors('sex', 'height', 'weight', 'activity_level', 'birth_date', 'sport_ids');
    const height = Number(form.height);
    const weight = Number(form.weight);

    if (!['male', 'female', 'other'].includes(form.sex)) {
        form.setError('sex', 'Selectionne ton sexe.');
    }

    if (form.height === '' || !Number.isInteger(height) || height < 50 || height > 300) {
        form.setError('height', 'La taille doit être un nombre entier entre 50 et 300 cm.');
    }

    if (form.weight === '' || Number.isNaN(weight) || weight < 20 || weight > 600) {
        form.setError('weight', 'Le poids doit être un nombre entre 20 et 600 kg.');
    }

    if (!activityLevelOptions.some((option) => option.value === form.activity_level)) {
        form.setError('activity_level', "Sélectionne un niveau d'activité.");
    }

    if (!form.birth_date) {
        form.setError('birth_date', 'La date de naissance est requise.');
    } else if (form.birth_date > todayDate.value) {
        form.setError('birth_date', 'La date de naissance ne peut pas être dans le futur.');
    } else if (form.birth_date > minimumBirthDate.value) {
        form.setError('birth_date', 'Tu dois avoir au moins 15 ans pour utiliser l\'application.');
    }

    return !(
        form.errors.sex
        || form.errors.height
        || form.errors.weight
        || form.errors.activity_level
        || form.errors.birth_date
        || form.errors.sport_ids
    );
}

function submit() {
    if (!validateProfileFields()) {
        return;
    }

    form.post(store.url(), {
        onError: (errors) => {
            const accountFields = ['first_name', 'pseudo', 'email', 'password', 'password_confirmation'];

            if (accountFields.some((field) => errors[field])) {
                currentStep.value = 'account';
            }
        },
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthBase
        title="Créer un compte"
        description="Renseigne l'essentiel, puis complète ton profil sportif."
    >
        <Head title="S'inscrire" />

        <div class="mb-7 grid grid-cols-2 gap-2 rounded-lg bg-evo-black/5 p-1 text-sm font-medium">
            <button
                type="button"
                class="flex items-center justify-center gap-2 rounded-md px-3 py-2 transition"
                :class="currentStep === 'account' ? 'bg-evo-black text-evo-white shadow-sm' : 'text-evo-black/60'"
                @click="goToAccount"
            >
                <UserRound class="size-4" />
                Compte
            </button>
            <button
                type="button"
                class="flex items-center justify-center gap-2 rounded-md px-3 py-2 transition"
                :class="currentStep === 'profile' ? 'bg-evo-black text-evo-white shadow-sm' : 'text-evo-black/60'"
                @click="goToProfile"
            >
                <Dumbbell class="size-4" />
                Profil
            </button>
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <section v-if="currentStep === 'account'" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="first_name">Nom</Label>
                        <Input
                            id="first_name"
                            v-model="form.first_name"
                            type="text"
                            required
                            autofocus
                            autocomplete="given-name"
                            placeholder="Célestin"
                            class="h-11 border-evo-black/15 bg-white/80"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="pseudo">Pseudo</Label>
                        <Input
                            id="pseudo"
                            v-model="form.pseudo"
                            type="text"
                            required
                            autocomplete="username"
                            placeholder="celestin"
                            class="h-11 border-evo-black/15 bg-white/80"
                        />
                    </div>
                </div>
                <div v-if="form.errors.first_name || form.errors.pseudo" class="grid gap-1">
                    <InputError :message="form.errors.first_name" />
                    <InputError :message="form.errors.pseudo" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-11 border-evo-black/15 bg-white/80"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="password">Mot de passe</Label>
                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="h-11 border-evo-black/15 bg-white/80"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmation</Label>
                        <PasswordInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="h-11 border-evo-black/15 bg-white/80"
                        />
                    </div>
                </div>
                <div v-if="form.errors.password || form.errors.password_confirmation" class="grid gap-1">
                    <InputError :message="form.errors.password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
                <InputError :message="accountValidationError" />

                <Button
                    type="button"
                    class="w-full"
                    :disabled="validatingAccount"
                    @click="goToProfile"
                >
                    <Spinner v-if="validatingAccount" />
                    Continuer
                    <ChevronRight class="size-4" />
                </Button>
            </section>

            <section v-if="currentStep === 'profile'" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="height">Taille</Label>
                        <div class="relative">
                            <Input
                                id="height"
                                v-model="form.height"
                                type="number"
                                min="50"
                                max="300"
                                required
                                placeholder="175"
                                class="h-11 border-evo-black/15 bg-white/80 pr-12"
                            />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-evo-black/45">cm</span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="weight">Poids</Label>
                        <div class="relative">
                            <Input
                                id="weight"
                                v-model="form.weight"
                                type="number"
                                min="20"
                                max="600"
                                step="0.1"
                                required
                                placeholder="72.5"
                                class="h-11 border-evo-black/15 bg-white/80 pr-12"
                            />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm text-evo-black/45">kg</span>
                        </div>
                    </div>
                </div>
                <div v-if="form.errors.height || form.errors.weight" class="grid gap-1">
                    <InputError :message="form.errors.height" />
                    <InputError :message="form.errors.weight" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="sex">Sexe</Label>
                        <select
                            id="sex"
                            v-model="form.sex"
                            required
                            class="h-11 w-full rounded-md border border-evo-black/15 bg-white/80 px-3 text-sm outline-none transition focus:border-evo-purple focus:ring-3 focus:ring-evo-purple/20"
                        >
                            <option value="" disabled>Sélectionner</option>
                            <option value="male">Homme</option>
                            <option value="female">Femme</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="grid gap-2">
                        <Label for="birth_date">Date de naissance</Label>
                        <Input
                            id="birth_date"
                            v-model="form.birth_date"
                            type="date"
                            :max="minimumBirthDate"
                            required
                            class="h-11 border-evo-black/15 bg-white/80"
                        />
                    </div>
                </div>
                <div v-if="form.errors.sex || form.errors.birth_date" class="grid gap-1">
                    <InputError :message="form.errors.sex" />
                    <InputError :message="form.errors.birth_date" />
                </div>

                <div class="grid gap-2">
                    <Label for="activity_level">Niveau d'activité</Label>
                    <select
                        id="activity_level"
                        v-model="form.activity_level"
                        required
                        class="h-11 w-full rounded-md border border-evo-black/15 bg-white/80 px-3 text-sm outline-none transition focus:border-evo-purple focus:ring-3 focus:ring-evo-purple/20"
                    >
                        <option
                            v-for="option in activityLevelOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.activity_level" />
                </div>

                <div class="grid gap-3">
                    <div class="flex items-center justify-between gap-3">
                        <Label>Sports pratiqués</Label>
                        <span class="text-xs font-medium text-evo-black/50">
                            {{ selectedSportsCount }} sélectionné{{ selectedSportsCount > 1 ? 's' : '' }}
                        </span>
                    </div>
                    <p class="text-xs text-evo-black/50">
                        Si ton sport n'est pas dans la liste, tu pourras le créer ensuite depuis l'application.
                    </p>
                    <div class="grid max-h-48 gap-2 overflow-y-auto rounded-lg border border-evo-black/10 bg-white/70 p-2 sm:grid-cols-2">
                        <label
                            v-for="sport in availableSports"
                            :key="sport.id"
                            class="flex cursor-pointer items-center justify-between gap-3 rounded-md border px-3 py-2 text-sm transition"
                            :class="form.sport_ids.includes(sport.id) ? 'border-evo-purple bg-evo-purple/10 text-evo-black' : 'border-transparent bg-white/70 text-evo-black/70 hover:border-evo-black/10'"
                        >
                            <span>{{ sport.name }}</span>
                            <input
                                type="checkbox"
                                class="sr-only"
                                :checked="form.sport_ids.includes(sport.id)"
                                @change="toggleSport(sport.id)"
                            />
                            <Check
                                class="size-4"
                                :class="form.sport_ids.includes(sport.id) ? 'text-evo-purple opacity-100' : 'opacity-0'"
                            />
                        </label>
                    </div>
                    <InputError :message="form.errors.sport_ids" />
                </div>

                <div class="grid gap-3 sm:grid-cols-[auto_1fr]">
                    <Button
                        type="button"
                        class="h-11 border-evo-black/15 bg-white/80 px-4"
                        @click="goToAccount"
                    >
                        <ArrowLeft class="size-4" />
                        Retour
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="form.processing" />
                        Créer un compte
                    </Button>
                </div>
            </section>

            <div class="text-center text-sm text-evo-black/60">
                Vous avez déjà un compte ?
                <TextLink :href="login()" class="font-medium text-evo-black underline underline-offset-4">
                    Se connecter
                </TextLink>
            </div>
        </form>
    </AuthBase>
</template>

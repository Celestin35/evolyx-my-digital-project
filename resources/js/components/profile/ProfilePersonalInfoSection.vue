<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { progress } from '@/routes';
import arrowDown from '../../../images/icons/arrow-down-purple.svg';
import {
    activityLevelOptions,
    formatActivityLevelLabel,
    formatSexLabel,
    sexOptions,
} from '@/lib/profile';

type ProfileUser = {
    id: number;
    first_name: string | null;
    pseudo: string | null;
    email: string;
    sex: string | null;
    height: number | null;
    birth_date: string | null;
    activity_level: string | null;
    age: number | null;
    current_weight: string | number | null;
    role: string | null;
};

const props = defineProps<{
    user: ProfileUser;
}>();

const isEditing = ref(false);

const personalInfoForm = useForm({
    first_name: props.user.first_name ?? '',
    sex: props.user.sex ?? '',
    height: props.user.height ?? '',
    birth_date: props.user.birth_date ?? '',
    activity_level: props.user.activity_level ?? '',
});

const formattedSex = computed(() => formatSexLabel(props.user.sex));
const formattedActivityLevel = computed(() =>
    formatActivityLevelLabel(props.user.activity_level),
);

const startEdit = () => {
    personalInfoForm.defaults({
        first_name: props.user.first_name ?? '',
        sex: props.user.sex ?? '',
        height: props.user.height ?? '',
        birth_date: props.user.birth_date ?? '',
        activity_level: props.user.activity_level ?? '',
    });
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isEditing.value = true;
};

const cancelEdit = () => {
    personalInfoForm.reset();
    personalInfoForm.clearErrors();
    isEditing.value = false;
};

const savePersonalInfo = () => {
    personalInfoForm.patch('/profile/personal-info', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div
        data-default-open="true"
        class="js-section self-start w-full rounded-lg bg-white p-6"
    >
        <button
            type="button"
            class="js-section-trigger flex w-full items-center justify-between text-left hover:cursor-pointer"
        >
            <h2 class="text-lg font-semibold">Informations personnelles</h2>
            <span>
                <img
                    :src="arrowDown"
                    alt="Fleche pour ouvrir"
                    class="js-open-arrow h-auto w-6 rotate-0"
                />
            </span>
        </button>

        <div class="js-section-content">
            <div class="space-y-2 pt-4">
                <div class="flex items-center justify-end">
                    <button
                        v-if="!isEditing"
                        type="button"
                        class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                        @click="startEdit"
                    >
                        Modifier
                    </button>
                </div>

                <template v-if="!isEditing">
                    <div v-if="user.first_name" class="flex items-center gap-1">
                        <p class="font-medium">Prenom :</p>
                        <p>{{ user.first_name }}</p>
                    </div>
                    <div v-if="formattedSex" class="flex items-center gap-1">
                        <p class="font-medium">Sexe :</p>
                        <p>{{ formattedSex }}</p>
                    </div>
                    <div v-if="user.height" class="flex items-center gap-1">
                        <p class="font-medium">Taille :</p>
                        <p>{{ user.height }} cm</p>
                    </div>
                    <div v-if="user.birth_date" class="flex items-center gap-1">
                        <p class="font-medium">Date de naissance :</p>
                        <p>{{ user.birth_date }}</p>
                    </div>
                    <div
                        v-if="formattedActivityLevel"
                        class="flex items-center gap-1"
                    >
                        <p class="font-medium">Niveau d'activite :</p>
                        <p>{{ formattedActivityLevel }}</p>
                    </div>
                    <div v-if="user.age" class="flex items-center gap-1">
                        <p class="font-medium">Age :</p>
                        <p>{{ user.age }}</p>
                    </div>
                    <div
                        v-if="user.current_weight"
                        class="flex items-center gap-1"
                    >
                        <p class="font-medium">Poids actuel :</p>
                        <p>{{ user.current_weight }} kg</p>
                    </div>
                </template>

                <div v-else class="space-y-4">
                    <div class="space-y-2">
                        <label for="personal_first_name" class="block font-medium">
                            Prenom
                        </label>
                        <input
                            id="personal_first_name"
                            v-model="personalInfoForm.first_name"
                            type="text"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p
                            v-if="personalInfoForm.errors.first_name"
                            class="text-sm text-red-600"
                        >
                            {{ personalInfoForm.errors.first_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_sex" class="block font-medium">
                            Sexe
                        </label>
                        <select
                            id="personal_sex"
                            v-model="personalInfoForm.sex"
                            class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                        >
                            <option
                                v-for="option in sexOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <p
                            v-if="personalInfoForm.errors.sex"
                            class="text-sm text-red-600"
                        >
                            {{ personalInfoForm.errors.sex }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="personal_height" class="block font-medium">
                            Taille
                        </label>
                        <input
                            id="personal_height"
                            v-model="personalInfoForm.height"
                            type="number"
                            min="100"
                            max="250"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p
                            v-if="personalInfoForm.errors.height"
                            class="text-sm text-red-600"
                        >
                            {{ personalInfoForm.errors.height }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="personal_birth_date"
                            class="block font-medium"
                        >
                            Date de naissance
                        </label>
                        <input
                            id="personal_birth_date"
                            v-model="personalInfoForm.birth_date"
                            type="date"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p
                            v-if="personalInfoForm.errors.birth_date"
                            class="text-sm text-red-600"
                        >
                            {{ personalInfoForm.errors.birth_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="personal_activity_level"
                            class="block font-medium"
                        >
                            Niveau d'activite
                        </label>
                        <select
                            id="personal_activity_level"
                            v-model="personalInfoForm.activity_level"
                            class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                        >
                            <option
                                v-for="option in activityLevelOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <p
                            v-if="personalInfoForm.errors.activity_level"
                            class="text-sm text-red-600"
                        >
                            {{ personalInfoForm.errors.activity_level }}
                        </p>
                    </div>

                    <div
                        class="rounded-lg border border-dashed border-neutral-300 p-4"
                    >
                        <p class="text-sm text-neutral-600">
                            Pour modifier ou ajouter une entree de poids,
                            rendez-vous sur votre suivi d'evolution.
                        </p>
                        <Link
                            :href="progress()"
                            class="mt-3 inline-flex rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90"
                        >
                            Gerer mes entrees de poids
                        </Link>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="personalInfoForm.processing"
                            @click="savePersonalInfo"
                        >
                            {{
                                personalInfoForm.processing
                                    ? 'Enregistrement...'
                                    : 'Enregistrer'
                            }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                            @click="cancelEdit"
                        >
                            Annuler
                        </button>
                        <p
                            v-if="personalInfoForm.recentlySuccessful"
                            class="text-sm text-neutral-600"
                        >
                            Enregistre.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

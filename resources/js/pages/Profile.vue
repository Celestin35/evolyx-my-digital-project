<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import HorizontalTabs from '@/components/HorizontalTabs.vue';
import ProfileGoalSection from '@/components/profile/ProfileGoalSection.vue';
import ProfilePersonalInfoSection from '@/components/profile/ProfilePersonalInfoSection.vue';
import ProfileSportsSection from '@/components/profile/ProfileSportsSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';

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
    sport_ids: number[];
    sports: Array<{
        id: number;
        name: string;
    }>;
};

type SportOption = {
    id: number;
    name: string;
};

type ActiveGoal = {
    target_weight: string | number | null;
    weekly_weight_goal: string | number | null;
    goal_end_date: string | null;
    goal_type: string | null;
};

const props = defineProps<{
    user: ProfileUser;
    availableSports: SportOption[];
    activeGoal: ActiveGoal | null;
}>();

const activeProfileTab = ref<'goal' | 'personal' | 'sports'>('goal');

const profileTabs = [
    {
        value: 'goal',
        label: 'Objectif de poids',
    },
    {
        value: 'personal',
        label: 'Infos personnelles',
    },
    {
        value: 'sports',
        label: 'Sports',
    },
] as const;
</script>

<template>
    <Head title="Profil" />

    <AppLayout title="Profil" subtitle="Gérez vos informations personnelles.">
        <div class="space-y-4">
            <section class="rounded-lg bg-evo-white p-4">
                <div>
                    <h2 class="text-lg font-semibold">Fenêtres du profil</h2>
                    <p class="mt-1 text-sm text-neutral-600">
                        Consultez et modifiez vos informations personnelles.
                    </p>
                </div>

                <HorizontalTabs
                    v-model="activeProfileTab"
                    :tabs="profileTabs"
                    aria-label="Fenêtres du profil"
                />
            </section>

            <div>
                <ProfileGoalSection
                    v-if="activeProfileTab === 'goal'"
                    :user="props.user"
                    :active-goal="props.activeGoal"
                />
                <ProfilePersonalInfoSection
                    v-else-if="activeProfileTab === 'personal'"
                    :user="props.user"
                />
                <ProfileSportsSection
                    v-else
                    :user="props.user"
                    :available-sports="props.availableSports"
                />
            </div>
        </div>
    </AppLayout>
</template>

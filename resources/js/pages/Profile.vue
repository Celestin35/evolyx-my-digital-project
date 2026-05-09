<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
</script>

<template>
    <Head title="Profil" />

    <AppLayout title="Profil" subtitle="Gérez vos informations personnelles.">
        <section class="flex flex-col gap-4 lg:flex-row">
            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <ProfilePersonalInfoSection :user="props.user" />
                <ProfileSportsSection :user="props.user" :available-sports="props.availableSports" />
            </div>

            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <ProfileGoalSection :user="props.user" :active-goal="props.activeGoal" />
            </div>
        </section>
    </AppLayout>
</template>

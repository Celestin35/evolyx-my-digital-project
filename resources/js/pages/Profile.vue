<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import gsap from 'gsap';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import ProfileGoalSection from '@/components/profile/ProfileGoalSection.vue';
import ProfilePersonalInfoSection from '@/components/profile/ProfilePersonalInfoSection.vue';
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

const sectionsRoot = ref<HTMLElement | null>(null);
const cleanups: Array<() => void> = [];

onMounted(() => {
    const root = sectionsRoot.value;
    if (!root) {
        return;
    }

    const sections = root.querySelectorAll<HTMLElement>('.js-section');

    sections.forEach((section) => {
        const trigger = section.querySelector<HTMLElement>('.js-section-trigger');
        const content = section.querySelector<HTMLElement>('.js-section-content');

        if (!trigger || !content) {
            return;
        }

        const tl = gsap.timeline({ paused: true });

        tl.to(content, {
            height: 'auto',
            opacity: 1,
            duration: 0.4,
            ease: 'power2.out',
        }).to(
            trigger.querySelector('.js-open-arrow'),
            {
                rotate: 180,
                duration: 0.4,
                ease: 'power2.out',
            },
            0,
        );

        const handleClick = () => {
            if (tl.reversed() || tl.progress() === 0) {
                tl.play();
                return;
            }

            tl.reverse();
        };

        gsap.set(content, { height: 0, opacity: 0, overflow: 'hidden' });
        trigger.addEventListener('click', handleClick);

        if (section.dataset.defaultOpen === 'true') {
            tl.play(0);
        } else {
            tl.reverse(0);
        }

        cleanups.push(() => {
            trigger.removeEventListener('click', handleClick);
            tl.kill();
        });
    });
});

onBeforeUnmount(() => {
    cleanups.forEach((cleanup) => cleanup());
    cleanups.length = 0;
});
</script>

<template>
    <Head title="Profil" />

    <AppLayout title="Profil" subtitle="Gerez vos informations personnelles.">
        <section ref="sectionsRoot" class="flex flex-col gap-4 lg:flex-row">
            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <ProfilePersonalInfoSection :user="props.user" :available-sports="props.availableSports" />
            </div>

            <div class="flex w-full flex-col gap-4 lg:w-1/2">
                <ProfileGoalSection :user="props.user" :active-goal="props.activeGoal" />
            </div>
        </section>
    </AppLayout>
</template>

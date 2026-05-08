<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { profile } from '@/routes';
import user from '../../../images/icons/user2-purple.svg';
defineProps<{
    title: String;
    subtitle?: String;
}>();
</script>

<template>
    <header
        class="hidden w-full rounded-lg bg-evo-white p-4 lg:block"
    >
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-evo-black">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-gray-600">
                    {{ subtitle }}
                </p>
            </div>
            <div>
                <Link :href="profile()" class="flex items-center gap-4">
                    <img :src="user" alt="Profile" class="h-auto w-7" />
                    <p class="text-lg font-medium">Profil</p>
                </Link>
            </div>
        </div>
    </header>
</template>

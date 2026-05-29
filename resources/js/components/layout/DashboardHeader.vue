<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { profile } from '@/routes';
import profileSvg from '../../../images/icons/profile.svg?raw';
defineProps<{
    title: string;
    subtitle?: string;
}>();

const { isCurrentOrParentUrl } = useCurrentUrl();

const isProfileActive = computed(() => isCurrentOrParentUrl(profile()));
</script>

<template>
    <header class="w-full rounded-lg bg-evo-white p-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-evo-black">{{ title }}</h1>
                <p
                    v-if="subtitle"
                    class="hidden text-sm text-gray-600 lg:block"
                >
                    {{ subtitle }}
                </p>
            </div>
            <div class="hidden lg:block">
                <Link
                    :href="profile()"
                    class="group flex items-center gap-4 text-black"
                >
                    <span
                        :class="[
                            isProfileActive
                                ? 'text-evo-orange'
                                : 'text-evo-purple lg:group-hover:text-evo-orange',
                            '[&_svg]:h-auto [&_svg]:w-7',
                        ]"
                        v-html="profileSvg"
                    />
                    <p class="text-lg font-medium">Profil</p>
                </Link>
            </div>
        </div>
    </header>
</template>

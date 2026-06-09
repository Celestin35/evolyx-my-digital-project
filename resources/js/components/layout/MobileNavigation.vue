<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import {
    community,
    dashboard,
    nutrition,
    progress,
    sessions,
} from '@/routes';
import communitySvg from '../../../images/icons/community.svg?raw';
import evolutionSvg from '../../../images/icons/evolution.svg?raw';
import homeSvg from '../../../images/icons/home.svg?raw';
import nutritionSvg from '../../../images/icons/nutrition.svg?raw';
import sessionsSvg from '../../../images/icons/sessions.svg?raw';

const { isCurrentOrParentUrl } = useCurrentUrl();

const navItems = computed(() => [
    {
        label: 'Accueil',
        href: dashboard(),
        icon: homeSvg,
        active: isCurrentOrParentUrl(dashboard()),
    },
    {
        label: 'Séances',
        href: sessions(),
        icon: sessionsSvg,
        active: isCurrentOrParentUrl(sessions()),
    },
    {
        label: 'Nutrition',
        href: nutrition(),
        icon: nutritionSvg,
        active: isCurrentOrParentUrl(nutrition()),
    },
    {
        label: 'Évolution',
        href: progress(),
        icon: evolutionSvg,
        active: isCurrentOrParentUrl(progress()),
    },
    {
        label: 'Communauté',
        href: community(),
        icon: communitySvg,
        active: isCurrentOrParentUrl(community()),
    },
]);
</script>

<template>
    <div class="pointer-events-none fixed inset-4 z-40 lg:hidden">
        <nav
            class="pointer-events-auto absolute right-0 bottom-0 left-0 rounded-full bg-evo-white px-4 py-2 shadow-sm"
            aria-label="Navigation principale"
        >
            <div class="grid grid-cols-5 items-end gap-1">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex min-w-0 flex-col items-center justify-end gap-1 text-center text-[9px] leading-none font-bold text-black"
                    :aria-current="item.active ? 'page' : undefined"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-md"
                    >
                        <span
                            :class="[
                                item.active
                                    ? 'text-evo-orange'
                                    : 'text-evo-purple',
                                '[&_svg]:h-7 [&_svg]:w-7',
                            ]"
                            v-html="item.icon"
                        />
                    </span>
                    <span class="max-w-full truncate">{{ item.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

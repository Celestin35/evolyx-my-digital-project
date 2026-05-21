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
import appleSvg from '../../../images/icons/apple-purple.svg';
import dashboardSvg from '../../../images/icons/dashboard-purple.svg';
import graphicSvg from '../../../images/icons/graphic2-purple.svg';
import sessionsSvg from '../../../images/icons/sessions-purple.svg';
import usersSvg from '../../../images/icons/users-purple.svg';

const { isCurrentOrParentUrl } = useCurrentUrl();

const navItems = computed(() => [
    {
        label: 'Accueil',
        href: dashboard(),
        icon: dashboardSvg,
        active: isCurrentOrParentUrl(dashboard()),
    },
    {
        label: 'Communaute',
        href: community(),
        icon: usersSvg,
        active: isCurrentOrParentUrl(community()),
    },
    {
        label: 'Nutrition',
        href: nutrition(),
        icon: appleSvg,
        active: isCurrentOrParentUrl(nutrition()),
    },
    {
        label: 'Evolution',
        href: progress(),
        icon: graphicSvg,
        active: isCurrentOrParentUrl(progress()),
    },
    {
        label: 'Seances',
        href: sessions(),
        icon: sessionsSvg,
        active: isCurrentOrParentUrl(sessions()),
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
                    class="flex min-w-0 flex-col items-center justify-end gap-1 text-center text-[9px] leading-none font-bold text-evo-black"
                    :aria-current="item.active ? 'page' : undefined"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-md"
                        :class="item.active ? 'bg-evo-purple' : ''"
                    >
                        <img
                            :src="item.icon"
                            alt=""
                            class="max-h-7 max-w-7 object-contain"
                            :class="item.active ? 'brightness-0 invert' : ''"
                        />
                    </span>
                    <span class="max-w-full truncate">{{ item.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

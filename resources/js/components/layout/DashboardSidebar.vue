<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import {
    community,
    dashboard,
    home,
    logout,
    nutrition,
    progress,
    sessions,
} from '@/routes';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show as showTwoFactor } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import communitySvg from '../../../images/icons/community.svg?raw';
import evolutionSvg from '../../../images/icons/evolution.svg?raw';
import homeSvg from '../../../images/icons/home.svg?raw';
import logoutSvg from '../../../images/icons/logout.svg?raw';
import nutritionSvg from '../../../images/icons/nutrition.svg?raw';
import sessionsSvg from '../../../images/icons/sessions.svg?raw';
import settingsSvg from '../../../images/icons/settings.svg?raw';
import logoEvolyxOrange from '../../../images/logo/logo-evolyx-orange.svg';

const { isCurrentOrParentUrl } = useCurrentUrl();

const isSettingsActive = computed(() =>
    [
        editProfile(),
        editPassword(),
        showTwoFactor(),
        editAppearance(),
    ].some((href) => isCurrentOrParentUrl(href)),
);

const navItems = computed(() => [
    {
        label: 'Accueil',
        href: dashboard(),
        icon: homeSvg,
        active: isCurrentOrParentUrl(dashboard()),
    },
    {
        label: 'Seances',
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
        label: 'Evolution',
        href: progress(),
        icon: evolutionSvg,
        active: isCurrentOrParentUrl(progress()),
    },
    {
        label: 'Communaute',
        href: community(),
        icon: communitySvg,
        active: isCurrentOrParentUrl(community()),
    },
]);
</script>

<template>
    <aside
        class="fixed left-4 top-4 hidden h-[calc(100dvh-2rem)] w-1/5 flex-col items-center justify-between rounded-lg bg-evo-white p-4 lg:flex"
    >
        <div class="w-full">
            <Link :href="home()" class="mb-4 block h-auto w-40">
                <img
                    :src="logoEvolyxOrange"
                    alt="Evolyx"
                    class="h-auto max-w-full"
                />
            </Link>

            <nav class="flex flex-col justify-center gap-4 text-lg font-medium">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="group flex items-center gap-4 text-black"
                    :aria-current="item.active ? 'page' : undefined"
                >
                    <span
                        :class="[
                            item.active
                                ? 'text-evo-orange'
                                : 'text-evo-purple lg:group-hover:text-evo-orange',
                            '[&_svg]:h-auto [&_svg]:w-7',
                        ]"
                        v-html="item.icon"
                    />
                    <p>{{ item.label }}</p>
                </Link>
            </nav>
        </div>
        <div class="w-full">
            <Link
                :href="editProfile()"
                class="group mb-4 flex items-center gap-4 text-left text-sm font-medium text-black"
            >
                <span
                    :class="[
                        isSettingsActive
                            ? 'text-evo-orange'
                            : 'text-evo-purple lg:group-hover:text-evo-orange',
                        '[&_svg]:h-auto [&_svg]:w-7',
                    ]"
                    v-html="settingsSvg"
                />
                <span>Parametres</span>
            </Link>
            <Link
                :href="logout()"
                method="post"
                as="button"
                class="group flex items-center gap-4 text-left text-sm font-medium text-black hover:cursor-pointer"
            >
                <span
                    class="text-evo-purple lg:group-hover:text-evo-orange [&_svg]:h-auto [&_svg]:w-7"
                    v-html="logoutSvg"
                />
                <span>Deconnexion</span>
            </Link>
        </div>
    </aside>
</template>

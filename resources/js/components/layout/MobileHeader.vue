<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { home, profile } from '@/routes';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show as showTwoFactor } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import profileSvg from '../../../images/icons/profile.svg?raw';
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
const isProfileActive = computed(() => isCurrentOrParentUrl(profile()));
</script>

<template>
    <header class="flex w-full justify-between items-center gap-3 mb-1 lg:hidden">        
        <Link
                :href="editProfile()"
                class="flex w-8 flex-col items-center gap-0.5 text-[9px] leading-none font-bold text-black"
                aria-label="Paramètres"
            >
                <span
                    :class="[
                        isSettingsActive
                            ? 'text-evo-orange'
                            : 'text-evo-purple',
                        '[&_svg]:h-6.5 [&_svg]:w-6.5',
                    ]"
                    v-html="settingsSvg"
                />
            </Link>

        <Link :href="home()" class="justify-self-center">
            <img
                :src="logoEvolyxOrange"
                alt="Evolyx"
                class="h-auto w-36 max-w-[42vw]"
            />
        </Link>
            <Link
                :href="profile()"
                class="flex w-8 flex-col items-center gap-0.5 text-[9px] leading-none font-bold text-black"
                aria-label="Profil"
            >
                <span
                    :class="[
                        isProfileActive ? 'text-evo-orange' : 'text-evo-purple',
                        '[&_svg]:h-6.5 [&_svg]:w-6.5',
                    ]"
                    v-html="profileSvg"
                />
            </Link>
    </header>
</template>

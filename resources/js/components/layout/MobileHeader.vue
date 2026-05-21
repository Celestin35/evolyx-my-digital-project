<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import { home, profile } from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import userSvg from '../../../images/icons/user2-purple.svg';
import logoEvolyxOrange from '../../../images/logo/logo-evolyx-orange.svg';
</script>

<template>
    <header class="grid grid-cols-[3.5rem_1fr_auto] items-center gap-3 lg:hidden">
        <div aria-hidden="true"></div>

        <Link :href="home()" class="justify-self-center">
            <img
                :src="logoEvolyxOrange"
                alt="Evolyx"
                class="h-auto w-36 max-w-[42vw]"
            />
        </Link>

        <div class="flex items-start gap-2">
            <Link
                :href="editProfile()"
                class="flex w-8 flex-col items-center gap-0.5 text-[9px] leading-none font-bold"
                aria-label="Parametres"
            >
                <Settings class="h-5 w-5 text-evo-purple" />
                <span>Reglages</span>
            </Link>
            <Link
                :href="profile()"
                class="flex w-8 flex-col items-center gap-0.5 text-[9px] leading-none font-bold"
                aria-label="Profil"
            >
                <img :src="userSvg" alt="" class="h-5 w-5 object-contain" />
                <span>Profil</span>
            </Link>
        </div>
    </header>
</template>

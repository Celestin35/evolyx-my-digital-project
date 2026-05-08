<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings } from 'lucide-vue-next';
import {
    community,
    dashboard,
    home,
    logout,
    nutrition,
    progress,
    sessions,
} from '@/routes';
import { edit as editProfile } from '@/routes/profile';
import appleSvg from '../../../images/icons/apple-purple.svg';
import dashboardSvg from '../../../images/icons/dashboard-purple.svg';
import exitSvg from '../../../images/icons/exit-purple.svg';
import graphicSvg from '../../../images/icons/graphic2-purple.svg';
import sessionsSvg from '../../../images/icons/sessions-purple.svg';
import usersSvg from '../../../images/icons/users-purple.svg';
import logoEvolyxOrange from '../../../images/logo/logo-evolyx-orange.svg';
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
                    :href="dashboard()"
                    class="hover:color-evo-orange flex items-center gap-4"
                >
                    <img
                        :src="dashboardSvg"
                        alt="Dashboard"
                        class="h-auto w-7"
                    />
                    <p>Accueil</p>
                </Link>
                <Link :href="sessions()" class="flex items-center gap-4">
                    <img :src="sessionsSvg" alt="Séances" class="h-auto w-7" />
                    <p>Séances</p>
                </Link>
                <Link :href="nutrition()" class="flex items-center gap-4">
                    <img :src="appleSvg" alt="Nutrition" class="h-auto w-7" />
                    <p>Nutrition</p>
                </Link>
                <Link :href="progress()" class="flex items-center gap-4">
                    <img :src="graphicSvg" alt="Évolution" class="h-auto w-7" />
                    <p>Évolution</p>
                </Link>
                <Link :href="community()" class="flex items-center gap-4">
                    <img :src="usersSvg" alt="Communauté" class="h-auto w-7" />
                    <p>Communauté</p>
                </Link>
            </nav>
        </div>
        <div class="w-full">
            <Link
                :href="editProfile()"
                class="mb-4 flex items-center gap-4 text-left text-sm font-medium"
            >
                <Settings class="h-auto w-7 text-evo-purple" />
                <span>Paramètres</span>
            </Link>
            <Link
                :href="logout()"
                method="post"
                as="button"
                class="flex items-center gap-4 text-left text-sm font-medium hover:cursor-pointer"
            >
                <img :src="exitSvg" alt="Déconnexion" class="h-auto w-7" />
                <span>Déconnexion</span>
            </Link>
        </div>
    </aside>
</template>

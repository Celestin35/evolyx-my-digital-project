<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Compte',
        href: editProfile(),
    },
    {
        title: 'Mot de passe',
        href: editPassword(),
    },
    {
        title: 'Double authentification',
        href: show(),
    },
    {
        title: 'Apparence',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col gap-4 lg:flex-row">
        <aside class="w-full max-w-xl lg:w-48">
            <nav class="flex flex-col space-y-1 space-x-0" aria-label="Parametres">
                <Link
                    v-for="item in sidebarNavItems"
                    :key="toUrl(item.href)"
                    :href="item.href"
                    :class="[
                        'flex w-full items-center gap-2 rounded-full px-4 py-2 text-evo-black hover:bg-neutral-100 dark:text-evo-white dark:hover:bg-neutral-800',
                        {
                            'bg-evo-black text-evo-white hover:bg-evo-black hover:text-evo-white dark:bg-evo-white dark:text-evo-black dark:hover:bg-evo-white dark:hover:text-evo-black':
                                isCurrentOrParentUrl(item.href),
                        },
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.title }}
                </Link>
            </nav>
        </aside>

        <Separator class="my-4 lg:hidden" />

        <div class="flex-1">
            <section class="space-y-4">
                <slot />
            </section>
        </div>
    </div>
</template>

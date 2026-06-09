<script setup lang="ts">
import { computed } from 'vue';
import HorizontalTabs from '@/components/HorizontalTabs.vue';
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

const settingsTabs = computed(() =>
    sidebarNavItems.map((item) => ({
        value: toUrl(item.href),
        label: item.title,
        href: item.href,
        active: isCurrentOrParentUrl(item.href),
    })),
);
</script>

<template>
    <div class="space-y-4">
        <section class="rounded-lg bg-evo-white p-4 dark:bg-neutral-900">
            <HorizontalTabs
                :tabs="settingsTabs"
                aria-label="Paramètres"
                :spaced="false"
            />
        </section>

        <section class="space-y-4">
            <slot />
        </section>
    </div>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Double authentification',
        href: show(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbs"
        title="Double authentification"
        subtitle="Renforcez la securite de votre connexion."
    >
        <Head title="Double authentification" />

        <h1 class="sr-only">Parametres de double authentification</h1>

        <SettingsLayout>
            <div class="rounded-lg bg-white p-6">
                <div class="space-y-6">
                    <Heading
                        variant="small"
                        title="Double authentification"
                        description="Gerez la double authentification de votre compte."
                    />

                    <div
                        v-if="!twoFactorEnabled"
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge variant="destructive">Desactivee</Badge>

                        <p class="text-neutral-600">
                            Lorsque vous activez la double authentification, un
                            code de securite supplementaire vous sera demande a la
                            connexion depuis votre application d'authentification.
                        </p>

                        <div>
                            <Button
                                v-if="hasSetupData"
                                class="rounded-full bg-evo-black px-4 py-2 text-evo-white hover:bg-evo-black/90"
                                @click="showSetupModal = true"
                            >
                                <ShieldCheck />Continuer la configuration
                            </Button>
                            <Form
                                v-else
                                v-bind="enable.form()"
                                @success="showSetupModal = true"
                                #default="{ processing }"
                            >
                                <Button
                                    type="submit"
                                    :disabled="processing"
                                    class="rounded-full bg-evo-black px-4 py-2 text-evo-white hover:bg-evo-black/90"
                                >
                                    <ShieldCheck />Activer la 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge variant="default">Activee</Badge>

                        <p class="text-neutral-600">
                            Avec la double authentification activee, un code
                            supplementaire vous sera demande a chaque connexion.
                        </p>

                        <TwoFactorRecoveryCodes />

                        <div class="relative inline">
                            <Form v-bind="disable.form()" #default="{ processing }">
                                <Button
                                    variant="destructive"
                                    type="submit"
                                    :disabled="processing"
                                    class="rounded-full px-4 py-2"
                                >
                                    <ShieldBan />
                                    Desactiver la 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <TwoFactorSetupModal
                        v-model:isOpen="showSetupModal"
                        :requiresConfirmation="requiresConfirmation"
                        :twoFactorEnabled="twoFactorEnabled"
                    />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

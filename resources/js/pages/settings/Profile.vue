<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
    activeSubscription: {
        plan_name: string | null;
        start_date: string | null;
        end_date: string | null;
        is_active: boolean;
    } | null;
    subscriptionPlans: Array<{
        name: string;
        price: string | number;
        ads_enabled: boolean;
        premium_features: boolean;
    }>;
};

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Paramètres du compte',
        href: edit(),
    },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
const successMessage = computed(() => page.props.flash?.success ?? null);

const accountInfoForm = useForm({
    pseudo: user.value.pseudo ?? '',
    email: user.value.email ?? '',
});

const subscriptionForm = useForm({
    subscription_plan_name: '',
});

const selectedSubscriptionPlanName = ref<string | null>(null);
const isSubscriptionModalOpen = ref(false);

const subscriptionPlanContent = {
    Free: {
        summary: 'Pour commencer simplement.',
        features: [
            "Accès aux fonctions de base de l'application",
            'Suivi du poids et consultation du profil',
            'Publicités actives',
        ],
    },
    Essential: {
        summary: 'Une formule légère sans publicités.',
        features: [
            'Suppression des publicités',
            'Confort de navigation amélioré',
            'Base idéale pour une utilisation régulière',
        ],
    },
    Premium: {
        summary: 'Le plan le plus complet pour aller plus loin.',
        features: [
            "Calcul des macronutriments selon l'objectif",
            'Modification plus poussée des objectifs',
            'Fonctions communautaires à venir',
        ],
    },
} satisfies Record<string, { summary: string; features: string[] }>;

const formattedActiveSubscriptionEndDate = computed(() => {
    if (!props.activeSubscription?.end_date) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    }).format(new Date(props.activeSubscription.end_date));
});

const formattedSubscriptionPlans = computed(() => {
    return props.subscriptionPlans.map((plan) => {
        const content = subscriptionPlanContent[plan.name] ?? {
            summary: "Détails de l'abonnement à définir.",
            features: [],
        };

        return {
            ...plan,
            priceLabel:
                Number(plan.price) === 0
                    ? 'Gratuit'
                    : `${Number(plan.price).toFixed(2)} EUR / mois`,
            summary: content.summary,
            features: content.features,
            isCurrent: props.activeSubscription?.plan_name === plan.name,
        };
    });
});

const selectedSubscriptionPlan = computed(() => {
    if (!selectedSubscriptionPlanName.value) {
        return null;
    }

    return formattedSubscriptionPlans.value.find(
        (plan) => plan.name === selectedSubscriptionPlanName.value,
    ) ?? null;
});

const subscriptionActionLabel = computed(() => {
    if (!selectedSubscriptionPlan.value) {
        return 'Passer à cet abonnement';
    }

    if (Number(selectedSubscriptionPlan.value.price) === 0) {
        return 'Confirmer le changement';
    }

    return 'Payer et changer';
});

const saveAccountInfo = () => {
    accountInfoForm.patch('/profile/account-info', {
        preserveScroll: true,
    });
};

const selectSubscriptionPlan = (planName: string) => {
    if (props.activeSubscription?.plan_name === planName) {
        return;
    }

    selectedSubscriptionPlanName.value = planName;
};

const openSubscriptionModal = () => {
    if (!selectedSubscriptionPlan.value) {
        return;
    }

    isSubscriptionModalOpen.value = true;
};

const closeSubscriptionModal = () => {
    isSubscriptionModalOpen.value = false;
};

const confirmSubscriptionChange = () => {
    if (!selectedSubscriptionPlan.value) {
        return;
    }

    subscriptionForm.subscription_plan_name = selectedSubscriptionPlan.value.name;
    subscriptionForm.post('/subscriptions', {
        preserveScroll: true,
        onSuccess: () => {
            isSubscriptionModalOpen.value = false;
            selectedSubscriptionPlanName.value = null;
        },
    });
};
</script>

<template>
    <AppLayout
        :breadcrumbs="breadcrumbItems"
        title="Paramètres du compte"
        subtitle="Gérez les informations du compte, l'abonnement et la sécurité."
    >
        <Head title="Paramètres du compte" />

        <h1 class="sr-only">Paramètres du compte</h1>

        <SettingsLayout>
            <div class="rounded-lg bg-evo-white p-4 dark:bg-neutral-900">
                <div class="flex flex-col space-y-4">
                    <Heading
                        variant="small"
                        title="Informations du compte"
                        description="Modifiez les informations principales de votre compte."
                    />

                    <div
                        v-if="successMessage"
                        class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700"
                    >
                        {{ successMessage }}
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="pseudo">Pseudo</Label>
                            <Input
                                id="pseudo"
                                v-model="accountInfoForm.pseudo"
                                class="evo-input mt-1 block text-evo-black"
                                required
                                autocomplete="nickname"
                                placeholder="Pseudo"
                            />
                            <InputError
                                class="mt-2"
                                :message="accountInfoForm.errors.pseudo"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Adresse email</Label>
                            <Input
                                id="email"
                                v-model="accountInfoForm.email"
                                type="email"
                                class="evo-input mt-1 block text-evo-black"
                                required
                                autocomplete="username"
                                placeholder="Adresse email"
                            />
                            <InputError
                                class="mt-2"
                                :message="accountInfoForm.errors.email"
                            />
                        </div>

                        <div
                            v-if="mustVerifyEmail && !user.email_verified_at"
                            class="rounded-lg border border-dashed border-neutral-300 p-4"
                        >
                            <p class="text-sm text-neutral-600">
                                Votre adresse email n'est pas vérifiée.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="font-medium text-evo-black underline underline-offset-4"
                                >
                                    Cliquez ici pour renvoyer l'email de vérification.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600"
                            >
                                Un nouveau lien de vérification a été envoyé.
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="accountInfoForm.processing"
                                data-test="update-profile-button"
                                @click="saveAccountInfo"
                            >
                                {{
                                    accountInfoForm.processing
                                        ? 'Enregistrement...'
                                        : 'Enregistrer'
                                }}
                            </Button>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="accountInfoForm.recentlySuccessful"
                                    class="text-sm text-neutral-600"
                                >
                                    Enregistré.
                                </p>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-evo-white p-4 dark:bg-neutral-900">
                <div class="space-y-4">
                    <Heading
                        variant="small"
                        title="Abonnement"
                        description="Consultez et changez votre formule actuelle."
                    />

                    <div
                        v-if="activeSubscription"
                        class="rounded-lg border border-neutral-200 p-4 bg-white"
                    >
                        <p class="font-medium">Abonnement actuel</p>
                        <div class="mt-2 space-y-2 text-sm text-neutral-700">
                            <p>
                                Plan :
                                <span class="font-semibold text-evo-black">
                                    {{ activeSubscription.plan_name ?? 'Non défini' }}
                                </span>
                            </p>
                            <p v-if="formattedActiveSubscriptionEndDate">
                                Renouvellement :
                                <span class="font-semibold text-evo-black">
                                    {{ formattedActiveSubscriptionEndDate }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="plan in formattedSubscriptionPlans"
                            :key="plan.name"
                            class="rounded-lg border p-4 transition"
                            :class="
                                plan.isCurrent
                                    ? 'border-evo-black bg-neutral-50'
                                    : selectedSubscriptionPlanName === plan.name
                                      ? 'border-evo-black bg-neutral-50'
                                      : 'border-neutral-200'
                            "
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium">{{ plan.name }}</p>
                                        <span
                                            v-if="plan.isCurrent"
                                            class="rounded-full bg-evo-black px-2 py-1 text-xs font-medium text-evo-white"
                                        >
                                            Actuel
                                        </span>
                                    </div>
                                    <p class="text-sm text-neutral-600">
                                        {{ plan.summary }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold">
                                    {{ plan.priceLabel }}
                                </p>
                            </div>

                            <div class="mt-3 space-y-2 text-sm text-neutral-700">
                                <p
                                    v-for="feature in plan.features"
                                    :key="feature"
                                >
                                    - {{ feature }}
                                </p>
                            </div>

                            <div class="mt-4">
                                <Button
                                    type="button"
                                    variant="transparent"
                                    :class="
                                        plan.isCurrent
                                            ? 'cursor-not-allowed border-neutral-200 text-neutral-400'
                                            : selectedSubscriptionPlanName ===
                                                plan.name
                                              ? 'bg-evo-black text-evo-white hover:cursor-pointer hover:opacity-90'
                                              : ''
                                    "
                                    :disabled="plan.isCurrent"
                                    @click="selectSubscriptionPlan(plan.name)"
                                >
                                    {{
                                        plan.isCurrent
                                            ? 'Abonnement actif'
                                            : selectedSubscriptionPlanName ===
                                                plan.name
                                              ? 'Plan sélectionné'
                                              : 'Choisir'
                                    }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="selectedSubscriptionPlan"
                        class="rounded-lg border border-neutral-200 bg-neutral-50 p-4"
                    >
                        <p class="font-medium">
                            Plan sélectionné :
                            {{ selectedSubscriptionPlan.name }}
                        </p>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{ selectedSubscriptionPlan.priceLabel }}
                        </p>
                        <Button
                            type="button"
                            class="mt-4"
                            @click="openSubscriptionModal"
                        >
                            Passer à cet abonnement
                        </Button>
                    </div>
                </div>
            </div>

            <DeleteUser />

            <div
                v-if="isSubscriptionModalOpen && selectedSubscriptionPlan"
                class="fixed inset-0 z-50 flex items-center justify-center bg-evo-black/50 px-4"
            >
                <div class="w-full max-w-md rounded-2xl bg-evo-white p-4 shadow-lg">
                    <div class="space-y-3">
                        <h2 class="text-lg font-semibold">Changer d'abonnement</h2>
                        <p class="text-sm text-neutral-600">
                            Vous allez passer sur l'abonnement
                            <span class="font-semibold text-evo-black">
                                {{ selectedSubscriptionPlan.name }}
                            </span>
                            pour
                            <span class="font-semibold text-evo-black">
                                {{ selectedSubscriptionPlan.priceLabel }}
                            </span>
                            .
                        </p>
                        <p class="text-sm text-neutral-600">
                            Simulation de paiement uniquement pour le moment.
                        </p>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-4">
                        <Button
                            type="button"
                            variant="transparent"
                            @click="closeSubscriptionModal"
                        >
                            Annuler
                        </Button>
                        <Button
                            type="button"
                            :disabled="subscriptionForm.processing"
                            @click="confirmSubscriptionChange"
                        >
                            {{
                                subscriptionForm.processing
                                    ? 'Traitement...'
                                    : subscriptionActionLabel
                            }}
                        </Button>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

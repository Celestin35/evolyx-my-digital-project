<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import HorizontalTabs from '@/components/HorizontalTabs.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PremiumFeatureGate from '@/components/PremiumFeatureGate.vue';
import { Button } from '@/components/ui/button';

type CommunityPerformanceMetric = {
    key: string;
    label: string;
    unit: string | null;
    value: number;
};

type CommunityPerformance = {
    exercise_name: string | null;
    sport_name: string | null;
    category_name: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    metric_values: CommunityPerformanceMetric[];
};

type CommunityPost = {
    id: number;
    author_name: string | null;
    is_own_post: boolean;
    title: string | null;
    content: string | null;
    published_at: string | null;
    performed_session: {
        id: number;
        workout_session_name: string | null;
        performed_at: string | null;
        completed_at: string | null;
        performances: CommunityPerformance[];
    };
};

type CommunityUser = {
    id: number;
    pseudo: string | null;
    display_name: string;
    initial: string;
    avatar_color: string;
    is_following: boolean;
};

type CommunityProfile = CommunityUser & {
    followers_count: number;
    following_count: number;
    posts_count: number;
    posts: CommunityPost[];
};

type CommunityPageProps = {
    flash?: {
        success?: string;
    };
    errors?: Record<string, string>;
};

const props = defineProps<{
    canAccessCommunity: boolean;
    activeSubscriptionPlan: string | null;
    followingFeed: CommunityPost[];
    ownPosts: CommunityPost[];
    following: CommunityUser[];
    followers: CommunityUser[];
}>();

const page = usePage<CommunityPageProps>();
const deletePostForm = useForm({});
const editPostForm = useForm({
    title: '',
    content: '',
});

const activeTab = ref<'feed' | 'relations' | 'mine'>('feed');
const communityTabs = [
    {
        value: 'feed',
        label: 'Feed',
    },
    {
        value: 'relations',
        label: 'Abonnements',
    },
    {
        value: 'mine',
        label: 'Mes posts',
    },
] as const;
const searchQuery = ref('');
const searchResults = ref<CommunityUser[]>([]);
const isSearching = ref(false);
const selectedProfile = ref<CommunityProfile | null>(null);
const editingPost = ref<CommunityPost | null>(null);
const isProfileLoading = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const flashSuccessMessage = computed(() => page.props.flash?.success);
const communityErrorMessage = computed(
    () => page.props.errors?.community ?? null,
);
const activePosts = computed(() =>
    activeTab.value === 'mine' ? props.ownPosts : props.followingFeed,
);

watch(searchQuery, (query) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    const trimmedQuery = query.trim();
    if (trimmedQuery.length < 2) {
        searchResults.value = [];
        isSearching.value = false;
        return;
    }

    isSearching.value = true;
    searchTimeout = setTimeout(async () => {
        const response = await fetch(
            `/community/users/search?q=${encodeURIComponent(trimmedQuery)}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            searchResults.value = [];
            isSearching.value = false;
            return;
        }

        const data = (await response.json()) as { users: CommunityUser[] };
        searchResults.value = data.users;
        isSearching.value = false;
    }, 250);
});

const formatDate = (date: string | null) => {
    if (!date) {
        return '-';
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(date));
};

const formatPublishedAt = (date: string | null) => {
    if (!date) {
        return '';
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

const formatPerformanceDetails = (performance: CommunityPerformance) => {
    const details: string[] = [];

    if (performance.metric_values.length > 0) {
        performance.metric_values.forEach((metricValue) => {
            details.push(
                `${metricValue.label}: ${metricValue.value}${metricValue.unit ? ` ${metricValue.unit}` : ''}`,
            );
        });
    } else {
        if (performance.weight !== null) {
            details.push(`${performance.weight.toFixed(2)} kg`);
        }

        if (performance.repetitions !== null) {
            details.push(`${performance.repetitions} rep`);
        }

        if (performance.duration_minutes !== null) {
            details.push(`${performance.duration_minutes.toFixed(2)} min`);
        }

        if (performance.distance_meters !== null) {
            details.push(`${performance.distance_meters.toFixed(0)} m`);
        }
    }

    return details.length > 0 ? details.join(' - ') : 'Séance validée';
};

const performanceDetailItems = (performance: CommunityPerformance) => {
    if (performance.metric_values.length > 0) {
        return performance.metric_values.map((metricValue) => ({
            label: metricValue.label,
            value: `${metricValue.value}${metricValue.unit ? ` ${metricValue.unit}` : ''}`,
        }));
    }

    const details: Array<{ label: string; value: string }> = [];

    if (performance.weight !== null) {
        details.push({
            label: 'Charge',
            value: `${performance.weight.toFixed(2)} kg`,
        });
    }

    if (performance.repetitions !== null) {
        details.push({
            label: 'Répétitions',
            value: `${performance.repetitions} rep`,
        });
    }

    if (performance.duration_minutes !== null) {
        details.push({
            label: 'Temps',
            value: `${performance.duration_minutes.toFixed(2)} min`,
        });
    }

    if (performance.distance_meters !== null) {
        details.push({
            label: 'Distance',
            value: `${performance.distance_meters.toFixed(0)} m`,
        });
    }

    return details;
};

const authorInitial = (post: CommunityPost) => {
    return (post.author_name ?? 'E').slice(0, 1).toUpperCase();
};

const followUser = (user: CommunityUser) => {
    router.post(
        `/community/users/${user.id}/follow`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                user.is_following = true;
            },
        },
    );
};

const unfollowUser = (user: CommunityUser) => {
    router.delete(`/community/users/${user.id}/follow`, {
        preserveScroll: true,
        onSuccess: () => {
            user.is_following = false;
        },
    });
};

const openProfile = async (user: CommunityUser) => {
    isProfileLoading.value = true;
    selectedProfile.value = null;

    const response = await fetch(`/community/users/${user.id}`, {
        headers: {
            Accept: 'application/json',
        },
    });

    if (response.ok) {
        const data = (await response.json()) as { user: CommunityProfile };
        selectedProfile.value = data.user;
    }

    isProfileLoading.value = false;
};

const closeProfile = () => {
    selectedProfile.value = null;
    isProfileLoading.value = false;
};

const openPostEditor = (post: CommunityPost) => {
    if (!post.is_own_post) {
        return;
    }

    editingPost.value = post;
    editPostForm.defaults({
        title: post.title ?? '',
        content: post.content ?? '',
    });
    editPostForm.reset();
    editPostForm.clearErrors();
};

const closePostEditor = () => {
    editingPost.value = null;
    editPostForm.reset();
    editPostForm.clearErrors();
};

const updateCommunityPost = () => {
    if (!editingPost.value) {
        return;
    }

    editPostForm.patch(`/community/posts/${editingPost.value.id}`, {
        preserveScroll: true,
        onSuccess: closePostEditor,
    });
};

const deleteCommunityPost = (post: CommunityPost) => {
    if (
        !window.confirm('Supprimer cette publication du feed communautaire ?')
    ) {
        return;
    }

    deletePostForm.delete(`/community/posts/${post.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Communauté" />

    <AppLayout
        title="Communauté"
        subtitle="Suivez des membres, consultez leur progression et gérez vos publications."
    >
        <PremiumFeatureGate
            :locked="!canAccessCommunity"
            feature-name="Feed communautaire"
            :current-plan="activeSubscriptionPlan"
            plain-when-unlocked
            description="Suivez des membres Premium et consultez les séances qu'ils partagent."
        >
            <div class="space-y-4">
                <section class="rounded-lg bg-evo-white p-4">
                    <div
                        class="flex flex-wrap items-start justify-between gap-3"
                    >
                        <div>
                            <h2 class="text-lg font-semibold">
                                Espace communautaire
                            </h2>
                            <p class="mt-1 text-sm text-neutral-600">
                                Le feed affiche uniquement les publications des
                                membres que vous suivez.
                            </p>
                        </div>
                        <Button :as="Link" href="/sessions#dernieres-seances">
                            Partager une séance
                        </Button>
                    </div>

                    <HorizontalTabs
                        v-model="activeTab"
                        :tabs="communityTabs"
                        aria-label="Espace communautaire"
                    />

                    <p
                        v-if="flashSuccessMessage"
                        class="mt-4 text-sm text-emerald-700"
                    >
                        {{ flashSuccessMessage }}
                    </p>
                    <p
                        v-if="communityErrorMessage"
                        class="mt-4 text-sm text-red-600"
                    >
                        {{ communityErrorMessage }}
                    </p>
                </section>

                <section v-if="activeTab === 'relations'" class="space-y-4">
                    <section class="rounded-lg bg-evo-white p-4">
                        <h2 class="text-lg font-semibold">
                            Rechercher un membre
                        </h2>
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Rechercher par pseudo"
                            class="evo-input mt-3 rounded-full"
                        />

                        <div
                            v-if="searchResults.length > 0"
                            class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="user in searchResults"
                                :key="user.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-neutral-400 bg-neutral-100 p-3"
                            >
                                <button
                                    type="button"
                                    class="flex min-w-0 items-center gap-3 text-left hover:cursor-pointer"
                                    @click="openProfile(user)"
                                >
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg font-semibold text-white"
                                        :style="{
                                            backgroundColor: user.avatar_color,
                                        }"
                                    >
                                        {{ user.initial }}
                                    </span>
                                    <span class="min-w-0">
                                        <span
                                            class="block truncate font-semibold"
                                        >
                                            {{ user.display_name }}
                                        </span>
                                        <span class="text-sm text-neutral-500">
                                            @{{ user.pseudo }}
                                        </span>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg bg-evo-purple px-3 py-1.5 text-xs font-medium text-evo-white hover:cursor-pointer hover:bg-evo-purple/90"
                                    @click="
                                        user.is_following
                                            ? unfollowUser(user)
                                            : followUser(user)
                                    "
                                >
                                    {{
                                        user.is_following
                                            ? 'Ne plus suivre'
                                            : 'Suivre'
                                    }}
                                </button>
                            </div>
                        </div>

                        <p
                            v-else-if="
                                searchQuery.trim().length >= 2 && !isSearching
                            "
                            class="mt-4 text-sm text-neutral-600"
                        >
                            Aucun membre trouvé.
                        </p>
                    </section>

                    <section class="grid gap-4 xl:grid-cols-2">
                        <div class="rounded-lg bg-evo-white p-4">
                            <h2 class="text-lg font-semibold">
                                Membres suivis
                            </h2>
                            <div
                                v-if="following.length > 0"
                                class="mt-4 space-y-3"
                            >
                                <div
                                    v-for="user in following"
                                    :key="user.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-neutral-400 bg-neutral-100 p-3"
                                >
                                    <button
                                        type="button"
                                        class="flex min-w-0 items-center gap-3 text-left hover:cursor-pointer"
                                        @click="openProfile(user)"
                                    >
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg font-semibold text-white"
                                            :style="{
                                                backgroundColor:
                                                    user.avatar_color,
                                            }"
                                        >
                                            {{ user.initial }}
                                        </span>
                                        <span class="min-w-0">
                                            <span
                                                class="block truncate font-semibold"
                                            >
                                                {{ user.display_name }}
                                            </span>
                                            <span
                                                class="text-sm text-neutral-500"
                                            >
                                                @{{ user.pseudo }}
                                            </span>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-evo-purple px-3 py-1.5 text-xs font-medium text-evo-white hover:cursor-pointer hover:bg-evo-purple/90"
                                        @click="unfollowUser(user)"
                                    >
                                        Ne plus suivre
                                    </button>
                                </div>
                            </div>
                            <p v-else class="mt-4 text-sm text-neutral-600">
                                Vous ne suivez encore personne.
                            </p>
                        </div>

                        <div class="rounded-lg bg-evo-white p-4">
                            <h2 class="text-lg font-semibold">Vos abonnés</h2>
                            <div
                                v-if="followers.length > 0"
                                class="mt-4 space-y-3"
                            >
                                <div
                                    v-for="user in followers"
                                    :key="user.id"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-neutral-400 bg-neutral-100 p-3"
                                >
                                    <button
                                        type="button"
                                        class="flex min-w-0 items-center gap-3 text-left hover:cursor-pointer"
                                        @click="openProfile(user)"
                                    >
                                        <span
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg font-semibold text-white"
                                            :style="{
                                                backgroundColor:
                                                    user.avatar_color,
                                            }"
                                        >
                                            {{ user.initial }}
                                        </span>
                                        <span class="min-w-0">
                                            <span
                                                class="block truncate font-semibold"
                                            >
                                                {{ user.display_name }}
                                            </span>
                                            <span
                                                class="text-sm text-neutral-500"
                                            >
                                                @{{ user.pseudo }}
                                            </span>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg bg-evo-purple px-3 py-1.5 text-xs font-medium text-evo-white hover:cursor-pointer hover:bg-evo-purple/90"
                                        @click="
                                            user.is_following
                                                ? unfollowUser(user)
                                                : followUser(user)
                                        "
                                    >
                                        {{
                                            user.is_following
                                                ? 'Suivi'
                                                : 'Suivre'
                                        }}
                                    </button>
                                </div>
                            </div>
                            <p v-else class="mt-4 text-sm text-neutral-600">
                                Aucun abonné pour le moment.
                            </p>
                        </div>
                    </section>
                </section>

                <section v-else class="space-y-4">
                    <article
                        v-for="post in activePosts"
                        :key="post.id"
                        class="overflow-hidden rounded-lg bg-evo-white"
                    >
                        <div class="p-4">
                            <div
                                class="flex flex-wrap items-start justify-between gap-4"
                            >
                                <div class="flex min-w-0 items-start gap-3">
                                    <div
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-evo-orange text-lg font-semibold text-white"
                                    >
                                        {{ authorInitial(post) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm text-neutral-500">
                                            {{
                                                post.author_name ??
                                                'Membre Evolyx'
                                            }}
                                            <span v-if="post.published_at">
                                                -
                                                {{
                                                    formatPublishedAt(
                                                        post.published_at,
                                                    )
                                                }}
                                            </span>
                                        </p>
                                        <h2
                                            class="mt-1 text-2xl leading-tight font-semibold"
                                        >
                                            {{
                                                post.title ??
                                                post.performed_session
                                                    .workout_session_name ??
                                                'Séance partagée'
                                            }}
                                        </h2>
                                    </div>
                                </div>

                                <div
                                    v-if="post.is_own_post"
                                    class="flex flex-wrap gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="transparent"
                                        class="px-3 py-1.5 text-xs"
                                        @click="openPostEditor(post)"
                                    >
                                        Modifier
                                    </Button>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        class="px-3 py-1.5 text-xs disabled:cursor-not-allowed disabled:opacity-50"
                                        :disabled="deletePostForm.processing"
                                        @click="deleteCommunityPost(post)"
                                    >
                                        Supprimer
                                    </Button>
                                </div>
                            </div>

                            <p
                                v-if="post.content"
                                class="mt-4 max-w-3xl text-base text-neutral-700"
                            >
                                {{ post.content }}
                            </p>

                            <div class="mt-5 rounded-xl border bg-white border-evo-orange p-4">
                                <div
                                    class="flex flex-wrap items-start justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-evo-black"
                                        >
                                            Séance partagée
                                        </p>
                                        <h3 class="sr-only">
                                            {{
                                                post.performed_session
                                                    .workout_session_name ??
                                                'Séance'
                                            }}
                                        </h3>
                                        <p
                                            class="mt-0.5 text-xs text-neutral-600"
                                        >
                                            Effectuée le
                                            {{
                                                formatDate(
                                                    post.performed_session
                                                        .completed_at,
                                                )
                                            }}
                                        </p>
                                    </div>
                                    <div
                                        class="rounded-lg border border-evo-orange bg-evo-white px-3 py-1 text-xs font-semibold text-evo-black"
                                    >
                                        {{
                                            post.performed_session.performances
                                                .length
                                        }}
                                        performance(s)
                                    </div>
                                </div>

                                <div
                                    v-if="
                                        post.performed_session.performances
                                            .length > 0
                                    "
                                    class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                                >
                                    <div
                                        v-for="(
                                            performance, performanceIndex
                                        ) in post.performed_session
                                            .performances"
                                        :key="`${post.id}-${performanceIndex}`"
                                        class="rounded-xl border border-neutral-400 bg-neutral-100 p-3"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <p class="font-semibold">
                                                {{
                                                    performance.exercise_name ??
                                                    'Exercice'
                                                }}
                                            </p>
                                            <span
                                                class="text-xs text-neutral-500"
                                            >
                                                {{
                                                    performance.category_name ??
                                                    performance.sport_name ??
                                                    'Sport'
                                                }}
                                            </span>
                                        </div>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <span
                                                v-for="detail in performanceDetailItems(
                                                    performance,
                                                )"
                                                :key="`${detail.label}-${detail.value}`"
                                                class="rounded-md border border-evo-orange bg-white px-2 py-1 text-xs text-neutral-600"
                                            >
                                                {{ detail.label }} :
                                                <span class="text-evo-black">
                                                    {{ detail.value }}
                                                </span>
                                            </span>
                                            <span
                                                v-if="
                                                    performanceDetailItems(
                                                        performance,
                                                    ).length === 0
                                                "
                                                class="rounded-md border border-evo-orange bg-white px-2 py-1 text-xs text-neutral-600"
                                            >
                                                {{
                                                    formatPerformanceDetails(
                                                        performance,
                                                    )
                                                }}
                                            </span>
                                        </div>
                                        <p
                                            v-if="performance.sport_name"
                                            class="mt-2 text-xs text-neutral-500"
                                        >
                                            {{ performance.sport_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <section
                        v-if="activePosts.length === 0"
                        class="rounded-lg bg-evo-white p-6"
                    >
                        <p
                            class="text-sm font-medium tracking-wide text-neutral-500 uppercase"
                        >
                            {{
                                activeTab === 'mine'
                                    ? 'Aucun post'
                                    : 'Feed vide'
                            }}
                        </p>
                        <h2 class="mt-2 text-2xl font-semibold">
                            {{
                                activeTab === 'mine'
                                    ? 'Vous n’avez pas encore partagé de séance'
                                    : 'Aucune séance partagée dans votre feed'
                            }}
                        </h2>
                        <p class="mt-2 text-sm text-neutral-600">
                            {{
                                activeTab === 'mine'
                                    ? 'Partagez une séance validée depuis la page Séances.'
                                    : 'Suivez des membres depuis l’onglet Abonnements pour alimenter ce feed.'
                            }}
                        </p>
                    </section>
                </section>
            </div>

            <template #locked-preview>
                <div class="space-y-4">
                    <section class="rounded-lg bg-evo-white p-4 opacity-60">
                        <h2 class="text-lg font-semibold">
                            Espace communautaire
                        </h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            Aperçu du feed communautaire Premium.
                        </p>
                    </section>
                </div>
            </template>
        </PremiumFeatureGate>

        <div
            v-if="editingPost"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="w-full max-w-xl rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Modifier la publication
                        </h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{
                                editingPost.performed_session
                                    .workout_session_name ?? 'Séance'
                            }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border border-neutral-300 px-3 py-1 text-sm hover:cursor-pointer"
                        @click="closePostEditor"
                    >
                        Fermer
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label for="edit_post_title" class="block font-medium">
                            Nom du post (optionnel)
                        </label>
                        <input
                            id="edit_post_title"
                            v-model="editPostForm.title"
                            type="text"
                            :placeholder="
                                editingPost.performed_session
                                    .workout_session_name ?? 'Séance partagée'
                            "
                            class="evo-input"
                        />
                        <p
                            v-if="editPostForm.errors.title"
                            class="text-sm text-red-600"
                        >
                            {{ editPostForm.errors.title }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="edit_post_content"
                            class="block font-medium"
                        >
                            Note (optionnel)
                        </label>
                        <textarea
                            id="edit_post_content"
                            v-model="editPostForm.content"
                            rows="3"
                            class="evo-input"
                        />
                        <p
                            v-if="editPostForm.errors.content"
                            class="text-sm text-red-600"
                        >
                            {{ editPostForm.errors.content }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            type="button"
                            :disabled="editPostForm.processing"
                            @click="updateCommunityPost"
                        >
                            {{
                                editPostForm.processing
                                    ? 'Enregistrement...'
                                    : 'Enregistrer'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="transparent"
                            @click="closePostEditor"
                        >
                            Annuler
                        </Button>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="selectedProfile || isProfileLoading"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="w-full max-w-xl rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold">Profil membre</h2>
                    <button
                        type="button"
                        class="rounded-full border border-neutral-300 px-3 py-1 text-sm hover:cursor-pointer"
                        @click="closeProfile"
                    >
                        Fermer
                    </button>
                </div>

                <p
                    v-if="isProfileLoading"
                    class="mt-4 text-sm text-neutral-600"
                >
                    Chargement...
                </p>

                <div v-else-if="selectedProfile" class="mt-4 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-lg text-xl font-semibold text-white"
                            :style="{
                                backgroundColor: selectedProfile.avatar_color,
                            }"
                        >
                            {{ selectedProfile.initial }}
                        </div>
                        <div>
                            <p class="text-xl font-semibold">
                                {{ selectedProfile.display_name }}
                            </p>
                            <p class="text-sm text-neutral-500">
                                @{{ selectedProfile.pseudo }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-lg border border-neutral-200 p-3">
                            <p class="text-lg font-semibold">
                                {{ selectedProfile.posts_count }}
                            </p>
                            <p class="text-xs text-neutral-500">posts</p>
                        </div>
                        <div class="rounded-lg border border-neutral-200 p-3">
                            <p class="text-lg font-semibold">
                                {{ selectedProfile.followers_count }}
                            </p>
                            <p class="text-xs text-neutral-500">abonnés</p>
                        </div>
                        <div class="rounded-lg border border-neutral-200 p-3">
                            <p class="text-lg font-semibold">
                                {{ selectedProfile.following_count }}
                            </p>
                            <p class="text-xs text-neutral-500">suivis</p>
                        </div>
                    </div>

                    <Button
                        type="button"
                        variant="transparent"
                        @click="
                            selectedProfile.is_following
                                ? unfollowUser(selectedProfile)
                                : followUser(selectedProfile)
                        "
                    >
                        {{
                            selectedProfile.is_following
                                ? 'Ne plus suivre'
                                : 'Suivre'
                        }}
                    </Button>

                    <div class="space-y-3">
                        <h3 class="font-semibold">Derniers posts</h3>
                        <div
                            v-if="selectedProfile.posts.length > 0"
                            class="space-y-2"
                        >
                            <div
                                v-for="post in selectedProfile.posts"
                                :key="post.id"
                                class="rounded-lg border border-neutral-200 p-3"
                            >
                                <p class="font-semibold">
                                    {{
                                        post.title ??
                                        post.performed_session
                                            .workout_session_name ??
                                        'Séance partagée'
                                    }}
                                </p>
                                <p class="mt-1 text-sm text-neutral-500">
                                    {{
                                        post.performed_session
                                            .workout_session_name ?? 'Séance'
                                    }}
                                    ·
                                    {{
                                        formatDate(
                                            post.performed_session.completed_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                        <p v-else class="text-sm text-neutral-600">
                            Aucun post partagé pour le moment.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

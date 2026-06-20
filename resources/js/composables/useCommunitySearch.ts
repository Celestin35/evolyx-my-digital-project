import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { CommunityProfile, CommunityUser } from '@/types/community';

export function useCommunitySearch() {
    const searchQuery = ref('');
    const searchResults = ref<CommunityUser[]>([]);
    const isSearching = ref(false);
    const selectedProfile = ref<CommunityProfile | null>(null);
    const isProfileLoading = ref(false);
    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

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

    return {
        searchQuery,
        searchResults,
        isSearching,
        selectedProfile,
        isProfileLoading,
        followUser,
        unfollowUser,
        openProfile,
        closeProfile,
    };
}

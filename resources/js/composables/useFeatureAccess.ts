import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type FeatureAccessState = {
    canAccessCommunity: boolean;
    canShareCommunityPost: boolean;
    canEditMacros: boolean;
    canViewPerformanceCharts: boolean;
    shouldDisplayAds: boolean;
    hasAdFreeExperience: boolean;
};

const defaultFeatures: FeatureAccessState = {
    canAccessCommunity: false,
    canShareCommunityPost: false,
    canEditMacros: false,
    canViewPerformanceCharts: false,
    shouldDisplayAds: false,
    hasAdFreeExperience: true,
};

export function useFeatureAccess() {
    const page = usePage();

    return computed<FeatureAccessState>(() => ({
        ...defaultFeatures,
        ...((page.props.features as Partial<FeatureAccessState> | null) ?? {}),
    }));
}

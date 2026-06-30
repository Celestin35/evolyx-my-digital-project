import { usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

export type AdsState = {
    enabled: boolean;
    has_ad_free_experience: boolean;
    popup_interval_minutes: number;
    close_delay_seconds: number;
};

export function useAds() {
    const page = usePage();

    const state = computed<AdsState>(() => {
        const sharedAds = page.props.ads as Partial<AdsState> | undefined;

        return {
            enabled: Boolean(sharedAds?.enabled),
            has_ad_free_experience: Boolean(
                sharedAds?.has_ad_free_experience ?? !sharedAds?.enabled,
            ),
            popup_interval_minutes: Number(
                sharedAds?.popup_interval_minutes ?? 5,
            ),
            close_delay_seconds: Number(sharedAds?.close_delay_seconds ?? 3),
        };
    });

    return reactive({
        get enabled() {
            return state.value.enabled;
        },
        get has_ad_free_experience() {
            return state.value.has_ad_free_experience;
        },
        get popup_interval_minutes() {
            return state.value.popup_interval_minutes;
        },
        get close_delay_seconds() {
            return state.value.close_delay_seconds;
        },
    });
}

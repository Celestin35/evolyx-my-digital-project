import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export type AdsState = {
    enabled: boolean;
    popup_interval_minutes: number;
    close_delay_seconds: number;
};

export function useAds() {
    const page = usePage();

    return computed<AdsState>(() => {
        const sharedAds = page.props.ads as Partial<AdsState> | undefined;

        return {
            enabled: Boolean(sharedAds?.enabled),
            popup_interval_minutes: Number(
                sharedAds?.popup_interval_minutes ?? 5,
            ),
            close_delay_seconds: Number(sharedAds?.close_delay_seconds ?? 3),
        };
    });
}

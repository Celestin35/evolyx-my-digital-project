import { computed, ref, toValue, type MaybeRefOrGetter } from 'vue';
import type { ProgressRange, WeightEntry } from '@/types/progress';

export const rangeOptions: Array<{ value: ProgressRange; label: string }> = [
    { value: '1m', label: '1M' },
    { value: '3m', label: '3M' },
    { value: '6m', label: '6M' },
    { value: '1y', label: '1A' },
    { value: 'all', label: 'Tout' },
];

export const getRangeCutoffDate = (range: ProgressRange) => {
    if (range === 'all') {
        return null;
    }

    const now = new Date();
    const cutoffDate = new Date(now);

    if (range === '1m') {
        cutoffDate.setMonth(now.getMonth() - 1);
    } else if (range === '3m') {
        cutoffDate.setMonth(now.getMonth() - 3);
    } else if (range === '6m') {
        cutoffDate.setMonth(now.getMonth() - 6);
    } else {
        cutoffDate.setFullYear(now.getFullYear() - 1);
    }

    return cutoffDate;
};

export function formatDateInput(date: Date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function useProgressRanges(
    weightEntries: MaybeRefOrGetter<WeightEntry[]>,
) {
    const selectedRange = ref<ProgressRange>('3m');
    const todayDate = computed(() => formatDateInput(new Date()));

    const filteredWeightEntries = computed(() => {
        const cutoffDate = getRangeCutoffDate(selectedRange.value);
        const entries = toValue(weightEntries);

        if (!cutoffDate) {
            return entries;
        }

        return entries.filter((entry) => {
            if (!entry.created_at) {
                return false;
            }

            return new Date(entry.created_at) >= cutoffDate;
        });
    });

    const chartKey = computed(() => {
        const lastEntryId = filteredWeightEntries.value.at(-1)?.id ?? 'none';

        return `${selectedRange.value}-${filteredWeightEntries.value.length}-${lastEntryId}`;
    });

    return {
        selectedRange,
        rangeOptions,
        filteredWeightEntries,
        chartKey,
        todayDate,
    };
}

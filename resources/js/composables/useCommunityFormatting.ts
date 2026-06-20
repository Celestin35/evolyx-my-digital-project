import type { CommunityPerformance, CommunityPost } from '@/types/community';

export function useCommunityFormatting() {
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

    return {
        formatDate,
        formatPublishedAt,
        formatPerformanceDetails,
        performanceDetailItems,
        authorInitial,
    };
}

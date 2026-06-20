export type WeightEntry = {
    id: number;
    weight: number;
    body_fat: number | null;
    created_at: string | null;
};

export type ProgressRange = '1m' | '3m' | '6m' | '1y' | 'all';
export type PerformanceMetric = string;

export type MetricOption = {
    value: PerformanceMetric;
    label: string;
    unit: string;
    value_type?: 'decimal' | 'integer';
    sort_order?: number;
};

export type PerformanceEntry = {
    id: number;
    performed_at: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    available_metrics: Array<{
        key: string;
        label: string;
        unit: string | null;
        value_type: 'decimal' | 'integer';
        sort_order: number;
    }>;
    metric_values: Record<string, number>;
    exercise_id: number;
    exercise_name: string;
    sport_id: number | null;
    sport_name: string | null;
};

export type SportOption = {
    id: number | string;
    name: string;
};

export type SelectOption = {
    id: number;
    name: string;
};

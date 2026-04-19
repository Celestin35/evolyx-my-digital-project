import type { InertiaLinkProps } from '@inertiajs/vue3';
import colorLib from '@kurkle/color';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

function valueOrDefault<T>(value: T | undefined, defaultValue: T): T {
    return value === undefined ? defaultValue : value;
}

let seed = Date.now();

export function srand(nextSeed: number) {
    seed = nextSeed;
}

export function rand(min = 0, max = 0) {
    const resolvedMin = valueOrDefault(min, 0);
    const resolvedMax = valueOrDefault(max, 0);

    seed = (seed * 9301 + 49297) % 233280;

    return resolvedMin + (seed / 233280) * (resolvedMax - resolvedMin);
}

export type NumberGeneratorConfig = {
    min?: number;
    max?: number;
    from?: number[];
    count?: number;
    decimals?: number;
    continuity?: number;
};

export function numbers(config: NumberGeneratorConfig = {}) {
    const min = valueOrDefault(config.min, 0);
    const max = valueOrDefault(config.max, 100);
    const from = valueOrDefault(config.from, []);
    const count = valueOrDefault(config.count, 8);
    const decimals = valueOrDefault(config.decimals, 8);
    const continuity = valueOrDefault(config.continuity, 1);
    const decimalFactor = Math.pow(10, decimals) || 0;
    const data: Array<number | null> = [];

    for (let index = 0; index < count; index += 1) {
        const value = (from[index] || 0) + rand(min, max);

        if (rand() <= continuity) {
            data.push(Math.round(decimalFactor * value) / decimalFactor);
        } else {
            data.push(null);
        }
    }

    return data;
}

export type ChartPoint = {
    x: number | null;
    y: number | null;
};

export function points(config: NumberGeneratorConfig = {}) {
    const xs = numbers(config);
    const ys = numbers(config);

    return xs.map((x, index) => ({
        x,
        y: ys[index] ?? null,
    })) satisfies ChartPoint[];
}

export type BubbleGeneratorConfig = NumberGeneratorConfig & {
    rmin?: number;
    rmax?: number;
};

export type BubblePoint = ChartPoint & {
    r: number;
};

export function bubbles(config: BubbleGeneratorConfig = {}) {
    const rmin = valueOrDefault(config.rmin, 0);
    const rmax = valueOrDefault(config.rmax, 0);

    return points(config).map((point) => ({
        ...point,
        r: rand(rmin, rmax),
    })) satisfies BubblePoint[];
}

export type LabelGeneratorConfig = {
    min?: number;
    max?: number;
    count?: number;
    decimals?: number;
    prefix?: string;
};

export function labels(config: LabelGeneratorConfig = {}) {
    const min = valueOrDefault(config.min, 0);
    const max = valueOrDefault(config.max, 100);
    const count = valueOrDefault(config.count, 8);
    const step = (max - min) / count;
    const decimals = valueOrDefault(config.decimals, 8);
    const decimalFactor = Math.pow(10, decimals) || 0;
    const prefix = valueOrDefault(config.prefix, '');
    const values: string[] = [];

    for (let value = min; value < max; value += step) {
        values.push(`${prefix}${Math.round(decimalFactor * value) / decimalFactor}`);
    }

    return values;
}

const MONTHS = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
] as const;

export type MonthGeneratorConfig = {
    count?: number;
    section?: number;
};

export function months(config: MonthGeneratorConfig = {}) {
    const count = valueOrDefault(config.count, 12);
    const values: string[] = [];

    for (let index = 0; index < count; index += 1) {
        const value = MONTHS[index % 12];
        values.push(config.section ? value.substring(0, config.section) : value);
    }

    return values;
}

const COLORS = [
    '#4dc9f6',
    '#f67019',
    '#f53794',
    '#537bc4',
    '#acc236',
    '#166a8f',
    '#00a950',
    '#58595b',
    '#8549ba',
] as const;

export function color(index: number) {
    return COLORS[index % COLORS.length];
}

export function transparentize(value: string, opacity = 0.5) {
    return colorLib(value).alpha(1 - opacity).rgbString();
}

export const CHART_COLORS = {
    white: 'rgb(247, 247, 245)',
    black: 'rgb(4, 3, 5)',
    purple: 'rgb(153, 102, 255)',
    orange: 'rgb(255, 129, 62)',
} as const;

const NAMED_COLORS = [
    CHART_COLORS.white,
    CHART_COLORS.black,
    CHART_COLORS.purple,
    CHART_COLORS.orange,
] as const;

export function namedColor(index: number) {
    return NAMED_COLORS[index % NAMED_COLORS.length];
}

export function newDate(days: number) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    return date;
}

export function newDateString(days: number) {
    return newDate(days).toISOString();
}

export function parseISODate(value: string) {
    return new Date(value);
}

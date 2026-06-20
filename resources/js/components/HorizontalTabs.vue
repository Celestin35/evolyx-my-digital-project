<script setup lang="ts">
import type { UrlMethodPair } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';

type TabItem = {
    value: string;
    label: string;
    href?: string | UrlMethodPair;
    active?: boolean;
};

withDefaults(
    defineProps<{
        tabs: readonly TabItem[];
        modelValue?: string;
        ariaLabel?: string;
        spaced?: boolean;
    }>(),
    {
        ariaLabel: '',
        spaced: true,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isActive = (tab: TabItem, modelValue?: string) =>
    tab.active === true || tab.value === modelValue;
</script>

<template>
    <nav
        class="flex flex-wrap gap-x-4 gap-y-2 border-b border-neutral-200 dark:border-neutral-700"
        :class="{ 'mt-4': spaced }"
        :aria-label="ariaLabel"
    >
        <template v-for="tab in tabs" :key="tab.value">
            <Link
                v-if="tab.href"
                :href="tab.href"
                class="border-b-2 px-3 py-2 text-sm font-medium transition hover:cursor-pointer"
                :class="
                    isActive(tab, modelValue)
                        ? 'border-evo-orange text-evo-black dark:text-evo-white'
                        : 'border-transparent text-neutral-500 hover:text-evo-black dark:hover:text-evo-white'
                "
            >
                {{ tab.label }}
            </Link>

            <button
                v-else
                type="button"
                class="border-b-2 px-3 py-2 text-sm font-medium transition hover:cursor-pointer"
                :class="
                    isActive(tab, modelValue)
                        ? 'border-evo-orange text-evo-black'
                        : 'border-transparent text-neutral-500 hover:text-evo-black'
                "
                @click="emit('update:modelValue', tab.value)"
            >
                {{ tab.label }}
            </button>
        </template>
    </nav>
</template>

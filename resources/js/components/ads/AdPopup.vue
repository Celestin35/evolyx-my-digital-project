<script setup lang="ts">
import { ArrowRight, BadgePercent } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    enabled: boolean;
    intervalMinutes: number;
    closeDelaySeconds: number;
}>();

const isOpen = ref(false);
const canClose = ref(false);
const remainingSeconds = ref(0);
const popupTimer = ref<number | null>(null);
const closeTimer = ref<number | null>(null);
const countdownTimer = ref<number | null>(null);

const storageKey = 'evolyx.nextAdPopupAt';

const intervalMs = computed(
    () => Math.max(1, props.intervalMinutes) * 60 * 1000,
);

const closeDelayMs = computed(
    () => Math.max(0, props.closeDelaySeconds) * 1000,
);

const clearTimer = (timer: typeof popupTimer) => {
    if (timer.value !== null) {
        window.clearTimeout(timer.value);
        timer.value = null;
    }
};

const clearIntervalTimer = () => {
    if (countdownTimer.value !== null) {
        window.clearInterval(countdownTimer.value);
        countdownTimer.value = null;
    }
};

const scheduleNextPopup = () => {
    clearTimer(popupTimer);

    if (!props.enabled || isOpen.value) {
        return;
    }

    const storedNextPopupAt = Number(window.localStorage.getItem(storageKey));
    const nextPopupAt =
        Number.isFinite(storedNextPopupAt) && storedNextPopupAt > 0
            ? storedNextPopupAt
            : Date.now() + intervalMs.value;

    window.localStorage.setItem(storageKey, String(nextPopupAt));

    popupTimer.value = window.setTimeout(
        () => {
            openPopup();
        },
        Math.max(0, nextPopupAt - Date.now()),
    );
};

const startCloseDelay = () => {
    clearTimer(closeTimer);
    clearIntervalTimer();

    canClose.value = closeDelayMs.value === 0;
    remainingSeconds.value = Math.ceil(closeDelayMs.value / 1000);

    if (canClose.value) {
        return;
    }

    countdownTimer.value = window.setInterval(() => {
        remainingSeconds.value = Math.max(0, remainingSeconds.value - 1);
    }, 1000);

    closeTimer.value = window.setTimeout(() => {
        canClose.value = true;
        clearIntervalTimer();
    }, closeDelayMs.value);
};

const openPopup = () => {
    if (!props.enabled) {
        return;
    }

    isOpen.value = true;
    startCloseDelay();
};

const closePopup = () => {
    if (!canClose.value) {
        return;
    }

    isOpen.value = false;
    window.localStorage.setItem(
        storageKey,
        String(Date.now() + intervalMs.value),
    );
    scheduleNextPopup();
};

watch(
    () => props.enabled,
    (enabled) => {
        if (enabled) {
            scheduleNextPopup();
            return;
        }

        isOpen.value = false;
        clearTimer(popupTimer);
        clearTimer(closeTimer);
        clearIntervalTimer();
    },
);

onMounted(() => {
    if (props.enabled) {
        scheduleNextPopup();
    }
});

onBeforeUnmount(() => {
    clearTimer(popupTimer);
    clearTimer(closeTimer);
    clearIntervalTimer();
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="enabled && isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/55 p-4"
                role="dialog"
                aria-modal="true"
                aria-label="Publicite"
            >
                <div
                    class="w-full max-w-xl rounded-lg bg-evo-white p-5 text-evo-black shadow-2xl dark:bg-neutral-900 dark:text-evo-white"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-evo-orange/15 text-evo-orange"
                            >
                                <BadgePercent class="h-6 w-6" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold">
                                    Espace publicitaire
                                </p>
                                <p
                                    class="text-sm text-neutral-600 dark:text-neutral-400"
                                >
                                    Contenu sponsorisé
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg hover:cursor-pointer border border-neutral-200 text-neutral-500 transition hover:border-evo-orange hover:text-evo-orange disabled:cursor-not-allowed disabled:opacity-40 dark:border-neutral-700"
                            :disabled="!canClose"
                            :aria-label="
                                canClose
                                    ? 'Fermer la publicite'
                                    : 'Fermeture bientot disponible'
                            "
                            @click="closePopup"
                        >
                            <ArrowRight v-if="canClose" class="h-5 w-5" />
                            <span v-else class="text-xs font-semibold">
                                {{ remainingSeconds }}
                            </span>
                        </button>
                    </div>

                    <div
                        class="mt-5 flex aspect-[16/7] items-center justify-center rounded-lg border border-dashed border-evo-purple/40 bg-white text-center dark:bg-neutral-950"
                    >
                        <div class="px-6">
                            <p class="text-xl font-bold">Espace publicitaire</p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

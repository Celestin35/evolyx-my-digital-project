<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

type ProfileUser = {
    sport_ids: number[];
    sports: Array<{
        id: number;
        name: string;
    }>;
};

type SportOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    user: ProfileUser;
    availableSports: SportOption[];
}>();

const isOpen = ref(true);
const isEditing = ref(false);

const sportsForm = useForm({
    sport_ids: props.user.sport_ids ?? [],
});

const selectedSportsLabel = computed(() => {
    if (!props.user.sports.length) {
        return 'Aucun sport selectionne.';
    }

    return props.user.sports.map((sport) => sport.name).join(', ');
});

const startEdit = () => {
    sportsForm.defaults({
        sport_ids: props.user.sport_ids ?? [],
    });
    sportsForm.reset();
    sportsForm.clearErrors();
    isEditing.value = true;
};

const cancelEdit = () => {
    sportsForm.reset();
    sportsForm.clearErrors();
    isEditing.value = false;
};

const saveSports = () => {
    sportsForm.patch('/profile/sports', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <div class="w-full self-start rounded-lg bg-white p-4">
        <button
            type="button"
            class="flex w-full items-center justify-between text-left hover:cursor-pointer"
            :aria-expanded="isOpen"
            aria-controls="profile-sports-content"
            @click="isOpen = !isOpen"
        >
            <h2 class="text-lg font-semibold">Sports pratiqués</h2>
            <ChevronDown
                class="h-6 w-6 text-evo-black transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                aria-hidden="true"
            />
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-1 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-1 opacity-0"
        >
            <div v-show="isOpen" id="profile-sports-content" class="space-y-4 pt-4">
                <div v-if="!isEditing" class="space-y-4">
                    <p class="text-sm text-neutral-700">
                        {{ selectedSportsLabel }}
                    </p>

                    <button
                        type="button"
                        class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                        @click="startEdit"
                    >
                        Modifier
                    </button>
                </div>

                <div v-else class="space-y-4">
                    <div class="grid gap-2 rounded-md border border-neutral-300 p-3 sm:grid-cols-2">
                        <label
                            v-for="sport in availableSports"
                            :key="sport.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                v-model="sportsForm.sport_ids"
                                type="checkbox"
                                :value="sport.id"
                                class="h-4 w-4 accent-evo-black"
                            />
                            <span>{{ sport.name }}</span>
                        </label>
                    </div>

                    <p v-if="sportsForm.errors.sport_ids" class="text-sm text-red-600">
                        {{ sportsForm.errors.sport_ids }}
                    </p>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:cursor-pointer hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="sportsForm.processing"
                            @click="saveSports"
                        >
                            {{ sportsForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:cursor-pointer hover:bg-neutral-100"
                            @click="cancelEdit"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

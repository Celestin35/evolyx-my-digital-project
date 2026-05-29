<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';

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
    <div class="w-full self-start rounded-lg bg-evo-white p-4">
        <h2 class="text-lg font-semibold">Sports pratiqués</h2>

        <div id="profile-sports-content" class="space-y-4 pt-4">
            <div v-if="!isEditing" class="space-y-4">
                <p class="text-sm text-neutral-700">
                    {{ selectedSportsLabel }}
                </p>

                <Button type="button" @click="startEdit"> Modifier </Button>
            </div>

            <div v-else class="space-y-4">
                <div
                    class="grid gap-2 rounded-md border border-neutral-300 p-3 sm:grid-cols-2"
                >
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

                <p
                    v-if="sportsForm.errors.sport_ids"
                    class="text-sm text-red-600"
                >
                    {{ sportsForm.errors.sport_ids }}
                </p>

                <div class="flex items-center gap-3">
                    <Button
                        type="button"
                        :disabled="sportsForm.processing"
                        @click="saveSports"
                    >
                        {{
                            sportsForm.processing
                                ? 'Enregistrement...'
                                : 'Enregistrer'
                        }}
                    </Button>
                    <Button
                        type="button"
                        variant="transparent"
                        @click="cancelEdit"
                    >
                        Annuler
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

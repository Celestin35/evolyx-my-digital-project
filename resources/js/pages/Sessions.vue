<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import VueCal from 'vue-cal';
import 'vue-cal/dist/vuecal.css';

type Sport = {
    id: number;
    name: string;
};

type ExerciseCategory = {
    id: number;
    name: string;
};

type AvailableExercise = {
    id: number;
    name: string;
    sport_id: number;
    sport_name: string | null;
    category_name: string | null;
    is_custom: boolean;
};

type WorkoutSession = {
    id: number;
    name: string;
    description: string | null;
    created_at: string | null;
    exercises: Array<{
        id: number;
        name: string;
        sport_id: number;
        sport_name: string | null;
    }>;
};

type PerformedSession = {
    id: number;
    workout_session_id: number;
    workout_session_name: string | null;
    performed_at: string | null;
    completed_at: string | null;
    notes: string | null;
    performances: Array<{
        exercise_id: number;
        weight: number | null;
        repetitions: number | null;
        duration_minutes: number | null;
        distance_meters: number | null;
    }>;
};

type SessionsPageProps = {
    flash?: {
        success?: string;
    };
};

const props = defineProps<{
    sports: Sport[];
    exerciseCategories: ExerciseCategory[];
    availableExercises: AvailableExercise[];
    workoutSessions: WorkoutSession[];
    performedSessions: PerformedSession[];
}>();

const page = usePage<SessionsPageProps>();

const flashSuccessMessage = computed(() => page.props.flash?.success);
const hasConfiguredSports = computed(() => props.sports.length > 0);

const workoutSessionForm = useForm({
    name: '',
    description: '',
    exercise_ids: [] as number[],
});

const customExerciseForm = useForm({
    name: '',
    description: '',
    sport_id: '',
    exercise_category_id: '',
});

const performedSessionForm = useForm({
    workout_session_id: '',
    performed_at: '',
    notes: '',
});
const selectedCalendarDate = ref<Date | null>(null);
const selectedPerformedSession = ref<PerformedSession | null>(null);
const wantsPerformanceEntry = ref<boolean | null>(null);

const completeSessionForm = useForm({
    notes: '',
    performances: [] as Array<{
        exercise_id: number;
        weight: string;
        repetitions: string;
        duration_minutes: string;
        distance_meters: string;
    }>,
});

const exerciseGroups = computed(() =>
    props.sports
        .map((sport) => ({
            sport,
            exercises: props.availableExercises.filter(
                (exercise) => exercise.sport_id === sport.id,
            ),
        }))
        .filter((group) => group.exercises.length > 0),
);

const calendarEvents = computed(() =>
    props.performedSessions
        .filter((session) => session.performed_at)
        .map((session) => {
            const startDate = new Date(session.performed_at as string);
            const endDate = new Date(startDate.getTime() + 60 * 60 * 1000);

            return {
                start: startDate,
                end: endDate,
                title: session.workout_session_name ?? 'Seance',
                content: session.notes ?? '',
                class: session.completed_at
                    ? 'evolyx-session-event evolyx-session-event--completed'
                    : 'evolyx-session-event evolyx-session-event--planned',
                performedSessionId: session.id,
            };
        }),
);

const recentCompletedSessions = computed(() =>
    [...props.performedSessions]
        .filter((session) => session.completed_at)
        .sort((firstSession, secondSession) => {
            return (
                new Date(secondSession.completed_at as string).getTime() -
                new Date(firstSession.completed_at as string).getTime()
            );
        })
        .slice(0, 5),
);
const selectedWorkoutSession = computed(() => {
    if (!selectedPerformedSession.value) {
        return null;
    }

    return (
        props.workoutSessions.find(
            (session) =>
                session.id ===
                selectedPerformedSession.value?.workout_session_id,
        ) ?? null
    );
});
const selectedCalendarDateLabel = computed(() => {
    if (!selectedCalendarDate.value) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(selectedCalendarDate.value);
});
const selectedPerformedSessionDateLabel = computed(() => {
    if (!selectedPerformedSession.value?.performed_at) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(selectedPerformedSession.value.performed_at));
});

const formatDateTimeLocal = (date: Date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
};

const onCalendarCellClick = (payload: Date | { date: Date }) => {
    const rawDate = payload instanceof Date ? payload : payload.date;
    const selectedDate = new Date(rawDate);

    // Month view uses date-only cells; we prefill a practical evening time.
    selectedDate.setHours(18, 0, 0, 0);

    selectedCalendarDate.value = selectedDate;
    performedSessionForm.performed_at = formatDateTimeLocal(selectedDate);
};

const isCompletableSession = (session: PerformedSession) => {
    if (session.completed_at || !session.performed_at) {
        return false;
    }

    const sessionDate = new Date(session.performed_at);
    const today = new Date();

    sessionDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);

    return sessionDate.getTime() <= today.getTime();
};

const onCalendarEventClick = (payload: unknown) => {
    const eventPayload = payload as {
        performedSessionId?: number;
        event?: { performedSessionId?: number };
    };
    const performedSessionId =
        eventPayload.performedSessionId ??
        eventPayload.event?.performedSessionId;

    const session = props.performedSessions.find(
        (performedSession) => performedSession.id === performedSessionId,
    );

    if (!session || !isCompletableSession(session)) {
        return;
    }

    selectedPerformedSession.value = session;
    wantsPerformanceEntry.value = null;
    completeSessionForm.clearErrors();
    completeSessionForm.notes = session.notes ?? '';
    completeSessionForm.performances =
        props.workoutSessions
            .find(
                (workoutSession) =>
                    workoutSession.id === session.workout_session_id,
            )
            ?.exercises.map((exercise) => {
                const existingPerformance = session.performances.find(
                    (performance) => performance.exercise_id === exercise.id,
                );

                return {
                    exercise_id: exercise.id,
                    weight: existingPerformance?.weight?.toString() ?? '',
                    repetitions:
                        existingPerformance?.repetitions?.toString() ?? '',
                    duration_minutes:
                        existingPerformance?.duration_minutes?.toString() ?? '',
                    distance_meters:
                        existingPerformance?.distance_meters?.toString() ?? '',
                };
            }) ?? [];
};

const closeCompleteSessionModal = () => {
    selectedPerformedSession.value = null;
    wantsPerformanceEntry.value = null;
    completeSessionForm.reset();
};

const toggleExercise = (exerciseId: number) => {
    const alreadySelected =
        workoutSessionForm.exercise_ids.includes(exerciseId);

    if (alreadySelected) {
        workoutSessionForm.exercise_ids =
            workoutSessionForm.exercise_ids.filter(
                (selectedId) => selectedId !== exerciseId,
            );

        return;
    }

    workoutSessionForm.exercise_ids.push(exerciseId);
};

const createWorkoutSession = () => {
    workoutSessionForm.post('/sessions/workout-sessions', {
        preserveScroll: true,
        onSuccess: () => {
            workoutSessionForm.reset();
        },
    });
};

const createCustomExercise = () => {
    customExerciseForm.post('/sessions/exercises', {
        preserveScroll: true,
        onSuccess: () => {
            customExerciseForm.reset();
        },
    });
};

const createPerformedSession = () => {
    performedSessionForm.post('/sessions/performed-sessions', {
        preserveScroll: true,
        onSuccess: () => {
            performedSessionForm.reset('notes');
        },
    });
};

const completeSelectedSession = () => {
    if (!selectedPerformedSession.value) {
        return;
    }

    completeSessionForm
        .transform((data) => ({
            notes: data.notes,
            performances: wantsPerformanceEntry.value ? data.performances : [],
        }))
        .patch(
            `/sessions/performed-sessions/${selectedPerformedSession.value.id}/complete`,
            {
                preserveScroll: true,
                onSuccess: closeCompleteSessionModal,
            },
        );
};
</script>

<template>
    <Head title="Seances" />

    <AppLayout
        title="Seances"
        subtitle="Organisez vos seances types et votre calendrier."
    >
        <section
            v-if="!hasConfiguredSports"
            class="mb-4 rounded-lg border border-dashed border-neutral-300 bg-white p-6"
        >
            <p class="text-sm font-medium text-evo-black">
                Aucun sport configure.
            </p>
            <p class="mt-2 text-sm text-neutral-600">
                Ajoutez au moins un sport dans votre profil pour creer des
                seances.
            </p>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-4">
                <section class="rounded-lg bg-white p-6">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold">
                            Calendrier des seances
                        </h2>
                        <p class="text-sm text-neutral-500">
                            {{ performedSessions.length }} seance(s)
                        </p>
                    </div>

                    <div
                        class="mt-4 overflow-hidden rounded-lg border border-neutral-200"
                    >
                        <VueCal
                            locale="fr"
                            active-view="month"
                            :time="false"
                            :disable-views="['years', 'year']"
                            events-on-month-view
                            :events="calendarEvents"
                            @cell-click="onCalendarCellClick"
                            @event-click="onCalendarEventClick"
                            style="height: 480px"
                        />
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">
                        Ajouter une seance au calendrier
                    </h2>
                    <p
                        v-if="selectedCalendarDateLabel"
                        class="mt-2 text-sm text-neutral-600"
                    >
                        Jour selectionne depuis le calendrier:
                        {{ selectedCalendarDateLabel }}
                    </p>

                    <div class="mt-4 space-y-4">
                        <div class="space-y-2">
                            <label
                                for="performed_workout_session"
                                class="block font-medium"
                            >
                                Seance type
                            </label>
                            <select
                                id="performed_workout_session"
                                v-model="
                                    performedSessionForm.workout_session_id
                                "
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                            >
                                <option value="">
                                    Selectionner une seance
                                </option>
                                <option
                                    v-for="session in workoutSessions"
                                    :key="session.id"
                                    :value="session.id"
                                >
                                    {{ session.name }}
                                </option>
                            </select>
                            <p
                                v-if="
                                    performedSessionForm.errors
                                        .workout_session_id
                                "
                                class="text-sm text-red-600"
                            >
                                {{
                                    performedSessionForm.errors
                                        .workout_session_id
                                }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label for="performed_at" class="block font-medium">
                                Date et heure
                            </label>
                            <input
                                id="performed_at"
                                v-model="performedSessionForm.performed_at"
                                type="datetime-local"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="performedSessionForm.errors.performed_at"
                                class="text-sm text-red-600"
                            >
                                {{ performedSessionForm.errors.performed_at }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="performed_notes"
                                class="block font-medium"
                            >
                                Notes (optionnel)
                            </label>
                            <textarea
                                id="performed_notes"
                                v-model="performedSessionForm.notes"
                                rows="3"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="performedSessionForm.errors.notes"
                                class="text-sm text-red-600"
                            >
                                {{ performedSessionForm.errors.notes }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90 disabled:opacity-50"
                                :disabled="performedSessionForm.processing"
                                @click="createPerformedSession"
                            >
                                {{
                                    performedSessionForm.processing
                                        ? 'Ajout...'
                                        : 'Ajouter au calendrier'
                                }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-4">
                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">Creer une seance type</h2>

                    <div class="mt-4 space-y-4">
                        <div class="space-y-2">
                            <label for="session_name" class="block font-medium"
                                >Nom</label
                            >
                            <input
                                id="session_name"
                                v-model="workoutSessionForm.name"
                                type="text"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="workoutSessionForm.errors.name"
                                class="text-sm text-red-600"
                            >
                                {{ workoutSessionForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="session_description"
                                class="block font-medium"
                            >
                                Description
                            </label>
                            <textarea
                                id="session_description"
                                v-model="workoutSessionForm.description"
                                rows="3"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="workoutSessionForm.errors.description"
                                class="text-sm text-red-600"
                            >
                                {{ workoutSessionForm.errors.description }}
                            </p>
                        </div>

                        <div class="space-y-3">
                            <p class="font-medium">
                                Exercices existants par sport
                            </p>
                            <div
                                class="max-h-64 space-y-3 overflow-y-auto rounded-lg border border-neutral-200 p-3"
                            >
                                <div
                                    v-for="group in exerciseGroups"
                                    :key="group.sport.id"
                                    class="space-y-2"
                                >
                                    <p
                                        class="text-sm font-semibold text-neutral-600"
                                    >
                                        {{ group.sport.name }}
                                    </p>
                                    <div class="space-y-2">
                                        <label
                                            v-for="exercise in group.exercises"
                                            :key="exercise.id"
                                            class="flex items-center justify-between gap-3 rounded-md border border-neutral-200 px-3 py-2 text-sm"
                                        >
                                            <span
                                                class="flex items-center gap-2"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :checked="
                                                        workoutSessionForm.exercise_ids.includes(
                                                            exercise.id,
                                                        )
                                                    "
                                                    class="h-4 w-4 accent-evo-black"
                                                    @change="
                                                        toggleExercise(
                                                            exercise.id,
                                                        )
                                                    "
                                                />
                                                <span>{{ exercise.name }}</span>
                                            </span>
                                            <span
                                                class="text-xs text-neutral-500"
                                            >
                                                {{ exercise.category_name }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <p
                                v-if="workoutSessionForm.errors.exercise_ids"
                                class="text-sm text-red-600"
                            >
                                {{ workoutSessionForm.errors.exercise_ids }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90 disabled:opacity-50"
                            :disabled="workoutSessionForm.processing"
                            @click="createWorkoutSession"
                        >
                            {{
                                workoutSessionForm.processing
                                    ? 'Creation...'
                                    : 'Creer la seance type'
                            }}
                        </button>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">
                        Creer un exercice personnalise
                    </h2>

                    <div class="mt-4 space-y-4">
                        <div class="space-y-2">
                            <label for="exercise_name" class="block font-medium"
                                >Nom</label
                            >
                            <input
                                id="exercise_name"
                                v-model="customExerciseForm.name"
                                type="text"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                            <p
                                v-if="customExerciseForm.errors.name"
                                class="text-sm text-red-600"
                            >
                                {{ customExerciseForm.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="exercise_sport"
                                class="block font-medium"
                                >Sport</label
                            >
                            <select
                                id="exercise_sport"
                                v-model="customExerciseForm.sport_id"
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                            >
                                <option value="">Selectionner un sport</option>
                                <option
                                    v-for="sport in sports"
                                    :key="sport.id"
                                    :value="sport.id"
                                >
                                    {{ sport.name }}
                                </option>
                            </select>
                            <p
                                v-if="customExerciseForm.errors.sport_id"
                                class="text-sm text-red-600"
                            >
                                {{ customExerciseForm.errors.sport_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="exercise_category"
                                class="block font-medium"
                                >Categorie</label
                            >
                            <select
                                id="exercise_category"
                                v-model="
                                    customExerciseForm.exercise_category_id
                                "
                                class="w-full rounded-md border border-neutral-300 bg-white px-4 py-2 focus:border-evo-black focus:outline-none"
                            >
                                <option value="">
                                    Selectionner une categorie
                                </option>
                                <option
                                    v-for="category in exerciseCategories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <p
                                v-if="
                                    customExerciseForm.errors
                                        .exercise_category_id
                                "
                                class="text-sm text-red-600"
                            >
                                {{
                                    customExerciseForm.errors
                                        .exercise_category_id
                                }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label
                                for="exercise_description"
                                class="block font-medium"
                            >
                                Description
                            </label>
                            <textarea
                                id="exercise_description"
                                v-model="customExerciseForm.description"
                                rows="3"
                                class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                            />
                        </div>

                        <button
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium text-evo-black transition hover:bg-neutral-100 disabled:opacity-50"
                            :disabled="customExerciseForm.processing"
                            @click="createCustomExercise"
                        >
                            {{
                                customExerciseForm.processing
                                    ? 'Creation...'
                                    : 'Creer l exercice'
                            }}
                        </button>
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6">
                    <h2 class="text-lg font-semibold">
                        Dernieres seances effectuees
                    </h2>

                    <div
                        v-if="recentCompletedSessions.length > 0"
                        class="mt-4 space-y-3"
                    >
                        <div
                            v-for="session in recentCompletedSessions"
                            :key="session.id"
                            class="rounded-lg border border-neutral-200 p-4"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <p class="font-semibold">
                                    {{
                                        session.workout_session_name ?? 'Seance'
                                    }}
                                </p>
                                <p class="text-xs text-emerald-700">Validee</p>
                            </div>
                            <p
                                v-if="session.notes"
                                class="mt-2 text-sm text-neutral-600"
                            >
                                {{ session.notes }}
                            </p>
                            <p class="mt-2 text-xs text-neutral-500">
                                {{ session.performances.length }} performance(s)
                            </p>
                        </div>
                    </div>
                    <p v-else class="mt-4 text-sm text-neutral-600">
                        Aucune seance effectuee pour le moment.
                    </p>

                    <p
                        v-if="flashSuccessMessage"
                        class="mt-4 text-sm text-emerald-700"
                    >
                        {{ flashSuccessMessage }}
                    </p>
                </section>
            </div>
        </section>

        <div
            v-if="selectedPerformedSession"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Valider la seance</h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{
                                selectedPerformedSession.workout_session_name ??
                                'Seance'
                            }}
                            <span v-if="selectedPerformedSessionDateLabel">
                                - {{ selectedPerformedSessionDateLabel }}
                            </span>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-full border border-neutral-300 px-3 py-1 text-sm"
                        @click="closeCompleteSessionModal"
                    >
                        Fermer
                    </button>
                </div>

                <div
                    v-if="wantsPerformanceEntry === null"
                    class="mt-6 space-y-4"
                >
                    <p class="font-medium">
                        Voulez-vous renseigner des performances sur cette seance
                        ?
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white"
                            @click="wantsPerformanceEntry = true"
                        >
                            Oui, ajouter des performances
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium"
                            @click="completeSelectedSession"
                        >
                            Non, valider la seance
                        </button>
                    </div>
                </div>

                <div v-else class="mt-6 space-y-5">
                    <div class="space-y-2">
                        <label for="complete_notes" class="block font-medium">
                            Notes (optionnel)
                        </label>
                        <textarea
                            id="complete_notes"
                            v-model="completeSessionForm.notes"
                            rows="3"
                            class="w-full rounded-md border border-neutral-300 px-4 py-2 focus:border-evo-black focus:outline-none"
                        />
                        <p
                            v-if="completeSessionForm.errors.notes"
                            class="text-sm text-red-600"
                        >
                            {{ completeSessionForm.errors.notes }}
                        </p>
                    </div>

                    <div v-if="wantsPerformanceEntry" class="space-y-4">
                        <div
                            v-for="(
                                performance, exerciseIndex
                            ) in completeSessionForm.performances"
                            :key="performance.exercise_id"
                            class="rounded-lg border border-neutral-200 p-4"
                        >
                            <p class="font-semibold">
                                {{
                                    selectedWorkoutSession?.exercises[
                                        exerciseIndex
                                    ]?.name ?? 'Exercice'
                                }}
                            </p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div class="space-y-1">
                                    <label class="text-sm font-medium"
                                        >Poids (kg)</label
                                    >
                                    <input
                                        v-model="performance.weight"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-md border border-neutral-300 px-3 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium"
                                        >Repetitions</label
                                    >
                                    <input
                                        v-model="performance.repetitions"
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="w-full rounded-md border border-neutral-300 px-3 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium"
                                        >Duree (min)</label
                                    >
                                    <input
                                        v-model="performance.duration_minutes"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-md border border-neutral-300 px-3 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-sm font-medium"
                                        >Distance (m)</label
                                    >
                                    <input
                                        v-model="performance.distance_meters"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full rounded-md border border-neutral-300 px-3 py-2 focus:border-evo-black focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <p
                        v-if="completeSessionForm.errors.performances"
                        class="text-sm text-red-600"
                    >
                        {{ completeSessionForm.errors.performances }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            class="rounded-full bg-evo-black px-4 py-2 text-sm font-medium text-evo-white transition hover:opacity-90 disabled:opacity-50"
                            :disabled="completeSessionForm.processing"
                            @click="completeSelectedSession"
                        >
                            {{
                                completeSessionForm.processing
                                    ? 'Validation...'
                                    : 'Valider la seance'
                            }}
                        </button>
                        <button
                            v-if="wantsPerformanceEntry"
                            type="button"
                            class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium"
                            @click="wantsPerformanceEntry = false"
                        >
                            Valider sans performances
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.vuecal__event.evolyx-session-event) {
    cursor: pointer;
}

:deep(.vuecal__event.evolyx-session-event--planned) {
    background-color: #f1e9ff;
    border: 1px solid #c7a8ff;
    color: #111111;
}

:deep(.vuecal__event.evolyx-session-event--completed) {
    background-color: #dcfce7;
    border: 1px solid #22c55e;
    color: #14532d;
}
</style>

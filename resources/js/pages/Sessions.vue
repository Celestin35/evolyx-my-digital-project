<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdInlineSlot from '@/components/ads/AdInlineSlot.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { useAds } from '@/composables/useAds';
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

type ExerciseMetric = {
    key: string;
    label: string;
    unit: string | null;
    value_type: 'decimal' | 'integer';
    is_required: boolean;
    is_primary: boolean;
    sort_order: number;
};

type AvailableExercise = {
    id: number;
    name: string;
    description: string | null;
    sport_id: number;
    sport_name: string | null;
    category_name: string | null;
    is_custom: boolean;
    metrics: ExerciseMetric[];
};

type WorkoutSession = {
    id: number;
    name: string;
    description: string | null;
    created_at: string | null;
    is_system: boolean;
    exercises: Array<{
        id: number;
        name: string;
        sport_id: number;
        sport_name: string | null;
        metrics: ExerciseMetric[];
    }>;
};

type PerformedSession = {
    id: number;
    workout_session_id: number;
    workout_session_name: string | null;
    performed_at: string | null;
    completed_at: string | null;
    community_post_id: number | null;
    notes: string | null;
    performances: Array<{
        exercise_id: number;
        weight: number | null;
        repetitions: number | null;
        duration_minutes: number | null;
        distance_meters: number | null;
        metric_values: Record<string, number>;
    }>;
};

type SessionsPageProps = {
    flash?: {
        success?: string;
    };
    errors?: Record<string, string>;
};

const props = defineProps<{
    sports: Sport[];
    exerciseCategories: ExerciseCategory[];
    availableExercises: AvailableExercise[];
    workoutSessions: WorkoutSession[];
    performedSessions: PerformedSession[];
    canShareToCommunity: boolean;
}>();

const ads = useAds();

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
const isWorkoutSessionModalOpen = ref(false);
const isCustomExerciseModalOpen = ref(false);
const activeLibraryTab = ref<'workout-sessions' | 'exercises'>(
    'workout-sessions',
);
const editingWorkoutSession = ref<WorkoutSession | null>(null);
const editingExercise = ref<AvailableExercise | null>(null);
const selectedShareSession = ref<PerformedSession | null>(null);
const isMobileCalendar = ref(false);
const mobileCalendarMediaQuery = '(max-width: 767px)';

let mobileCalendarQuery: MediaQueryList | null = null;
let onMobileCalendarChange: ((event: MediaQueryListEvent) => void) | null =
    null;

const completeSessionForm = useForm({
    notes: '',
    performances: [] as Array<{
        exercise_id: number;
        weight: string;
        repetitions: string;
        duration_minutes: string;
        distance_meters: string;
        metrics: Record<string, string>;
    }>,
});

const workoutSessionEditForm = useForm({
    name: '',
    description: '',
    exercise_ids: [] as number[],
});

const customExerciseEditForm = useForm({
    name: '',
    description: '',
    sport_id: '',
    exercise_category_id: '',
});

const deleteWorkoutSessionForm = useForm({});
const deleteExerciseForm = useForm({});
const shareSessionForm = useForm({
    performed_session_id: null as number | null,
    title: '',
    content: '',
});

const sortUserContentFirst = <T>(
    firstItem: T,
    secondItem: T,
    isUserContent: (item: T) => boolean,
) => {
    const firstIsUserContent = isUserContent(firstItem);
    const secondIsUserContent = isUserContent(secondItem);

    if (firstIsUserContent === secondIsUserContent) {
        return 0;
    }

    return firstIsUserContent ? -1 : 1;
};

const sortedAvailableExercises = computed(() =>
    [...props.availableExercises].sort((firstExercise, secondExercise) =>
        sortUserContentFirst(
            firstExercise,
            secondExercise,
            (exercise) => exercise.is_custom,
        ),
    ),
);

const sortedWorkoutSessions = computed(() =>
    [...props.workoutSessions].sort((firstSession, secondSession) =>
        sortUserContentFirst(
            firstSession,
            secondSession,
            (session) => !session.is_system,
        ),
    ),
);

const normalizeId = (id: number | string) => Number(id);

const exerciseGroups = computed(() =>
    props.sports
        .map((sport) => ({
            sport,
            exercises: sortedAvailableExercises.value.filter(
                (exercise) =>
                    normalizeId(exercise.sport_id) === normalizeId(sport.id),
            ),
        }))
        .filter((group) => group.exercises.length > 0),
);

const customExercises = computed(() =>
    sortedAvailableExercises.value.filter((exercise) => exercise.is_custom),
);

const libraryErrorMessage = computed(
    () =>
        page.props.errors?.workout_session ??
        page.props.errors?.exercise ??
        null,
);
const communityErrorMessage = computed(
    () => page.props.errors?.community ?? null,
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
                title: session.workout_session_name ?? 'Séance',
                content: session.notes ?? '',
                class: session.completed_at
                    ? 'evolyx-session-event evolyx-session-event--completed'
                    : 'evolyx-session-event evolyx-session-event--planned',
                performedSessionId: session.id,
            };
        }),
);
const calendarActiveView = computed(() =>
    isMobileCalendar.value ? 'day' : 'week',
);
const calendarDisabledViews = computed(() =>
    isMobileCalendar.value ? ['years', 'year', 'month'] : ['years', 'year'],
);
const calendarHeight = computed(() =>
    isMobileCalendar.value ? '430px' : '500px',
);
const calendarKey = computed(() =>
    isMobileCalendar.value ? 'sessions-calendar-mobile' : 'sessions-calendar',
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
const selectedWorkoutSessionHasExercises = computed(() => {
    return (selectedWorkoutSession.value?.exercises.length ?? 0) > 0;
});
const metricInputStep = (metric: ExerciseMetric) => {
    return metric.value_type === 'integer' ? '1' : '0.01';
};
const getExerciseMetrics = (exerciseId: number) => {
    return (
        selectedWorkoutSession.value?.exercises.find(
            (exercise) => exercise.id === exerciseId,
        )?.metrics ?? []
    );
};
const getExistingMetricValue = (
    performance: PerformedSession['performances'][number] | undefined,
    metricKey: string,
) => {
    if (!performance) {
        return '';
    }

    const metricValue = performance.metric_values?.[metricKey];

    if (metricValue !== undefined && metricValue !== null) {
        return metricValue.toString();
    }

    const legacyValues: Record<string, number | null> = {
        weight_kg: performance.weight,
        repetitions: performance.repetitions,
        duration_minutes: performance.duration_minutes,
        distance_meters: performance.distance_meters,
    };

    return legacyValues[metricKey]?.toString() ?? '';
};
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

const selectedShareSessionDateLabel = computed(() => {
    if (!selectedShareSession.value?.completed_at) {
        return null;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(selectedShareSession.value.completed_at));
});

const formatPerformanceSummary = (
    performance: PerformedSession['performances'][number],
) => {
    const details: string[] = [];
    const metricValues = Object.entries(performance.metric_values ?? {});

    if (metricValues.length > 0) {
        metricValues.slice(0, 3).forEach(([metricKey, value]) => {
            details.push(`${metricKey}: ${value}`);
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

    return details.length > 0 ? details.join(' - ') : 'Performance renseignee';
};

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
                    metrics: Object.fromEntries(
                        exercise.metrics.map((metric) => [
                            metric.key,
                            getExistingMetricValue(
                                existingPerformance,
                                metric.key,
                            ),
                        ]),
                    ),
                };
            }) ?? [];
};

const closeCompleteSessionModal = () => {
    selectedPerformedSession.value = null;
    wantsPerformanceEntry.value = null;
    completeSessionForm.reset();
};

const closeWorkoutSessionModal = () => {
    isWorkoutSessionModalOpen.value = false;
    workoutSessionForm.reset();
    workoutSessionForm.clearErrors();
};

const openWorkoutSessionModal = () => {
    workoutSessionForm.reset();
    workoutSessionForm.clearErrors();
    isWorkoutSessionModalOpen.value = true;
};

const closeCustomExerciseModal = () => {
    isCustomExerciseModalOpen.value = false;
    customExerciseForm.reset();
    customExerciseForm.clearErrors();
};

const openCustomExerciseModal = () => {
    customExerciseForm.reset();
    customExerciseForm.clearErrors();
    isCustomExerciseModalOpen.value = true;
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

const toggleEditExercise = (exerciseId: number) => {
    const alreadySelected =
        workoutSessionEditForm.exercise_ids.includes(exerciseId);

    if (alreadySelected) {
        workoutSessionEditForm.exercise_ids =
            workoutSessionEditForm.exercise_ids.filter(
                (selectedId) => selectedId !== exerciseId,
            );

        return;
    }

    workoutSessionEditForm.exercise_ids.push(exerciseId);
};

const workoutSessionSportNames = (session: WorkoutSession) => {
    const sportNames = new Set(
        session.exercises
            .map((exercise) => exercise.sport_name)
            .filter((sportName): sportName is string => Boolean(sportName)),
    );

    return [...sportNames].join(', ') || 'Aucun sport';
};

const openWorkoutSessionEditor = (session: WorkoutSession) => {
    editingWorkoutSession.value = session;
    workoutSessionEditForm.defaults({
        name: session.name,
        description: session.description ?? '',
        exercise_ids: session.exercises.map((exercise) => exercise.id),
    });
    workoutSessionEditForm.reset();
    workoutSessionEditForm.clearErrors();
};

const closeWorkoutSessionEditor = () => {
    editingWorkoutSession.value = null;
    workoutSessionEditForm.reset();
    workoutSessionEditForm.clearErrors();
};

const updateWorkoutSession = () => {
    if (!editingWorkoutSession.value) {
        return;
    }

    workoutSessionEditForm.patch(
        `/sessions/workout-sessions/${editingWorkoutSession.value.id}`,
        {
            preserveScroll: true,
            onSuccess: closeWorkoutSessionEditor,
        },
    );
};

const duplicateWorkoutSession = (session: WorkoutSession) => {
    workoutSessionForm.name = `${session.name} copie`;
    workoutSessionForm.description = session.description ?? '';
    workoutSessionForm.exercise_ids = session.exercises.map(
        (exercise) => exercise.id,
    );
    workoutSessionForm.post('/sessions/workout-sessions', {
        preserveScroll: true,
        onSuccess: () => {
            workoutSessionForm.reset();
        },
    });
};

const deleteWorkoutSession = (session: WorkoutSession) => {
    if (!window.confirm(`Supprimer la séance "${session.name}" ?`)) {
        return;
    }

    deleteWorkoutSessionForm.delete(
        `/sessions/workout-sessions/${session.id}`,
        {
            preserveScroll: true,
        },
    );
};

const openExerciseEditor = (exercise: AvailableExercise) => {
    if (!exercise.is_custom) {
        return;
    }

    editingExercise.value = exercise;
    customExerciseEditForm.defaults({
        name: exercise.name,
        description: exercise.description ?? '',
        sport_id: exercise.sport_id.toString(),
        exercise_category_id:
            props.exerciseCategories
                .find((category) => category.name === exercise.category_name)
                ?.id.toString() ?? '',
    });
    customExerciseEditForm.reset();
    customExerciseEditForm.clearErrors();
};

const closeExerciseEditor = () => {
    editingExercise.value = null;
    customExerciseEditForm.reset();
    customExerciseEditForm.clearErrors();
};

const updateExercise = () => {
    if (!editingExercise.value) {
        return;
    }

    customExerciseEditForm.patch(
        `/sessions/exercises/${editingExercise.value.id}`,
        {
            preserveScroll: true,
            onSuccess: closeExerciseEditor,
        },
    );
};

const deleteExercise = (exercise: AvailableExercise) => {
    if (!exercise.is_custom) {
        return;
    }

    if (!window.confirm(`Supprimer l'exercice "${exercise.name}" ?`)) {
        return;
    }

    deleteExerciseForm.delete(`/sessions/exercises/${exercise.id}`, {
        preserveScroll: true,
    });
};

const createWorkoutSession = () => {
    workoutSessionForm.post('/sessions/workout-sessions', {
        preserveScroll: true,
        onSuccess: closeWorkoutSessionModal,
    });
};

const createCustomExercise = () => {
    customExerciseForm.post('/sessions/exercises', {
        preserveScroll: true,
        onSuccess: closeCustomExerciseModal,
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

const openShareSessionModal = (session: PerformedSession) => {
    if (
        session.community_post_id ||
        !session.completed_at ||
        !props.canShareToCommunity
    ) {
        return;
    }

    selectedShareSession.value = session;
    shareSessionForm.defaults({
        performed_session_id: session.id,
        title: '',
        content: '',
    });
    shareSessionForm.reset();
    shareSessionForm.clearErrors();
};

const closeShareSessionModal = () => {
    selectedShareSession.value = null;
    shareSessionForm.reset();
    shareSessionForm.clearErrors();
};

const sharePerformedSession = () => {
    if (!selectedShareSession.value) {
        return;
    }

    shareSessionForm.performed_session_id = selectedShareSession.value.id;
    shareSessionForm.post('/community/posts', {
        preserveScroll: true,
        onSuccess: closeShareSessionModal,
    });
};

onMounted(() => {
    mobileCalendarQuery = window.matchMedia(mobileCalendarMediaQuery);
    isMobileCalendar.value = mobileCalendarQuery.matches;

    onMobileCalendarChange = (event) => {
        isMobileCalendar.value = event.matches;
    };

    mobileCalendarQuery.addEventListener('change', onMobileCalendarChange);
});

onBeforeUnmount(() => {
    if (!mobileCalendarQuery || !onMobileCalendarChange) {
        return;
    }

    mobileCalendarQuery.removeEventListener('change', onMobileCalendarChange);
});
</script>

<template>
    <Head title="Séances" />

    <AppLayout
        title="Séances"
        subtitle="Organisez vos séances types et votre calendrier."
    >
        <section
            v-if="!hasConfiguredSports"
            class="mb-4 rounded-lg border border-dashed border-neutral-300 bg-evo-white p-4"
        >
            <p class="text-sm font-medium text-evo-black">
                Aucun sport configuré.
            </p>
            <p class="mt-2 text-sm text-neutral-600">
                Ajoutez au moins un sport dans votre profil pour créer des
                séances.
            </p>
        </section>

        <div class="space-y-4 max-lg:pb-23">
            <section class="rounded-lg bg-evo-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">
                        Calendrier des séances
                    </h2>
                    <p class="text-sm text-neutral-500">
                        {{ performedSessions.length }} séance(s)
                    </p>
                </div>

                <div
                    class="sessions-calendar mt-4 overflow-hidden rounded-xl border border-neutral-200"
                >
                    <VueCal
                        :key="calendarKey"
                        locale="fr"
                        class="evolyx-calendar"
                        :active-view="calendarActiveView"
                        :time="false"
                        :disable-views="calendarDisabledViews"
                        events-on-month-view
                        :events="calendarEvents"
                        @cell-click="onCalendarCellClick"
                        @event-click="onCalendarEventClick"
                        :style="{ height: calendarHeight }"
                    />
                </div>
            </section>

            <section class="rounded-lg bg-evo-white p-4">
                <h2 class="text-lg font-semibold">
                    Ajouter une séance au calendrier
                </h2>
                <p
                    v-if="selectedCalendarDateLabel"
                    class="mt-2 text-sm font-medium text-neutral-600"
                >
                    Jour sélectionné depuis le calendrier :
                    {{ selectedCalendarDateLabel }}
                </p>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="space-y-2">
                        <label
                            for="performed_workout_session"
                            class="block font-medium"
                        >
                            Séance
                        </label>
                        <select
                            id="performed_workout_session"
                            v-model="performedSessionForm.workout_session_id"
                            class="evo-input px-3 text-sm sm:px-4 sm:text-base"
                        >
                            <option value="">Sélectionner une séance</option>
                            <option
                                v-for="session in sortedWorkoutSessions"
                                :key="session.id"
                                :value="session.id"
                            >
                                {{ session.name }}
                            </option>
                        </select>
                        <p
                            v-if="
                                performedSessionForm.errors.workout_session_id
                            "
                            class="text-sm text-red-600"
                        >
                            {{ performedSessionForm.errors.workout_session_id }}
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
                            class="evo-input"
                        />
                        <p
                            v-if="performedSessionForm.errors.performed_at"
                            class="text-sm text-red-600"
                        >
                            {{ performedSessionForm.errors.performed_at }}
                        </p>
                    </div>

                    <div class="space-y-2 lg:col-span-2">
                        <label for="performed_notes" class="block font-medium">
                            Notes (optionnel)
                        </label>
                        <textarea
                            id="performed_notes"
                            v-model="performedSessionForm.notes"
                            rows="3"
                            class="evo-input"
                        />
                        <p
                            v-if="performedSessionForm.errors.notes"
                            class="text-sm text-red-600"
                        >
                            {{ performedSessionForm.errors.notes }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 lg:col-span-2">
                        <Button
                            type="button"
                            :disabled="performedSessionForm.processing"
                            @click="createPerformedSession"
                        >
                            {{
                                performedSessionForm.processing
                                    ? 'Ajout...'
                                    : 'Ajouter au calendrier'
                            }}
                        </Button>
                    </div>
                </div>
            </section>

            <AdInlineSlot :enabled="ads.enabled" />

            <section class="rounded-lg bg-evo-white p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Bibliothèque</h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            Gérez vos séances types et vos exercices.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <Button
                            v-if="activeLibraryTab === 'workout-sessions'"
                            type="button"
                            @click="openWorkoutSessionModal"
                        >
                            Créer une séance
                        </Button>
                        <Button
                            v-else
                            type="button"
                            @click="openCustomExerciseModal"
                        >
                            Créer un exercice
                        </Button>
                    </div>
                </div>

                <div
                    class="mt-4 flex flex-wrap gap-2 border-b border-neutral-200"
                >
                    <button
                        type="button"
                        class="border-b-2 px-3 py-2 text-sm font-medium transition hover:cursor-pointer"
                        :class="
                            activeLibraryTab === 'workout-sessions'
                                ? 'border-evo-orange text-evo-black'
                                : 'border-transparent text-neutral-500 hover:text-evo-black'
                        "
                        @click="activeLibraryTab = 'workout-sessions'"
                    >
                        Séances types
                    </button>
                    <button
                        type="button"
                        class="border-b-2 px-3 py-2 text-sm font-medium transition hover:cursor-pointer"
                        :class="
                            activeLibraryTab === 'exercises'
                                ? 'border-evo-orange text-evo-black'
                                : 'border-transparent text-neutral-500 hover:text-evo-black'
                        "
                        @click="activeLibraryTab = 'exercises'"
                    >
                        Exercices
                    </button>
                </div>

                <p v-if="libraryErrorMessage" class="mt-4 text-sm text-red-600">
                    {{ libraryErrorMessage }}
                </p>

                <div
                    v-if="activeLibraryTab === 'workout-sessions'"
                    class="mt-4"
                >
                    <div
                        v-if="sortedWorkoutSessions.length > 0"
                        class="overflow-hidden rounded-lg border border-neutral-200"
                    >
                        <div
                            v-for="session in sortedWorkoutSessions"
                            :key="session.id"
                            class="grid gap-3 border-b border-neutral-200 bg-white p-4 last:border-b-0 lg:grid-cols-[1.2fr_1fr_auto]"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold">
                                        {{ session.name }}
                                    </p>
                                    <span
                                        v-if="session.exercises.length === 0"
                                        class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700"
                                    >
                                        Incomplète
                                    </span>
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="
                                            session.is_system
                                                ? 'bg-evo-purple text-evo-white'
                                                : 'bg-evo-orange text-evo-white'
                                        "
                                    >
                                        {{
                                            session.is_system
                                                ? 'Evolyx'
                                                : 'Personnel'
                                        }}
                                    </span>
                                </div>
                                <p
                                    v-if="session.description"
                                    class="mt-1 text-sm text-neutral-600"
                                >
                                    {{ session.description }}
                                </p>
                                <p
                                    v-if="session.exercises.length === 0"
                                    class="mt-2 text-sm text-red-600"
                                >
                                    Aucun exercice. Ajoutez-en au moins un pour
                                    saisir des performances.
                                </p>
                            </div>

                            <div class="text-sm text-neutral-600">
                                <p>
                                    {{ session.exercises.length }} exercice(s)
                                </p>
                                <p class="mt-1">
                                    {{ workoutSessionSportNames(session) }}
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap items-start gap-2 lg:justify-end"
                            >
                                <Button
                                    v-if="!session.is_system"
                                    type="button"
                                    class="px-3 py-1.5 text-sm"
                                    @click="openWorkoutSessionEditor(session)"
                                >
                                    Modifier
                                </Button>
                                <Button
                                    type="button"
                                    class="px-3 py-1.5 text-sm"
                                    @click="duplicateWorkoutSession(session)"
                                >
                                    Dupliquer
                                </Button>
                                <Button
                                    v-if="!session.is_system"
                                    type="button"
                                    variant="destructive"
                                    class="px-3 py-1.5 text-sm"
                                    :disabled="
                                        deleteWorkoutSessionForm.processing
                                    "
                                    @click="deleteWorkoutSession(session)"
                                >
                                    Supprimer
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-neutral-600">
                        Aucune séance créée pour le moment.
                    </p>
                </div>

                <div v-else class="mt-4">
                    <div
                        v-if="sortedAvailableExercises.length > 0"
                        class="overflow-hidden rounded-lg border border-neutral-200"
                    >
                        <div
                            v-for="exercise in sortedAvailableExercises"
                            :key="exercise.id"
                            class="grid gap-3 border-b border-neutral-200 bg-white p-4 last:border-b-0 lg:grid-cols-[1.2fr_1fr_1fr_auto]"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold">
                                        {{ exercise.name }}
                                    </p>
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="
                                            exercise.is_custom
                                                ? 'bg-evo-orange text-evo-white'
                                                : 'bg-evo-purple text-evo-white'
                                        "
                                    >
                                        {{
                                            exercise.is_custom
                                                ? 'Personnel'
                                                : 'Evolyx'
                                        }}
                                    </span>
                                </div>
                                <p
                                    v-if="exercise.description"
                                    class="mt-1 text-sm text-neutral-600"
                                >
                                    {{ exercise.description }}
                                </p>
                            </div>

                            <div class="text-sm text-neutral-600">
                                <p>{{ exercise.sport_name ?? 'Sport' }}</p>
                                <p class="mt-1">
                                    {{ exercise.category_name ?? 'Catégorie' }}
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap gap-2 text-xs text-neutral-600"
                            >
                                <span
                                    v-for="metric in exercise.metrics"
                                    :key="metric.key"
                                    class="h-fit rounded-full bg-neutral-100 px-2 py-1"
                                >
                                    {{ metric.label }}
                                </span>
                            </div>

                            <div
                                class="flex flex-wrap items-start gap-2 lg:justify-end"
                            >
                                <Button
                                    v-if="exercise.is_custom"
                                    type="button"
                                    class="px-3 py-1.5 text-sm"
                                    @click="openExerciseEditor(exercise)"
                                >
                                    Modifier
                                </Button>
                                <Button
                                    v-if="exercise.is_custom"
                                    type="button"
                                    variant="destructive"
                                    class="px-3 py-1.5 text-sm"
                                    :disabled="deleteExerciseForm.processing"
                                    @click="deleteExercise(exercise)"
                                >
                                    Supprimer
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-neutral-600">
                        Aucun exercice disponible pour vos sports.
                    </p>
                </div>
            </section>

            <section id="dernieres-seances" class="rounded-lg bg-evo-white p-4">
                <h2 class="text-lg font-semibold">
                    Dernières séances effectuées
                </h2>

                <div
                    v-if="recentCompletedSessions.length > 0"
                    class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <div
                        v-for="session in recentCompletedSessions"
                        :key="session.id"
                        class="rounded-lg border border-neutral-200 bg-white p-4"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold">
                                {{ session.workout_session_name ?? 'Séance' }}
                            </p>
                            <p class="text-xs text-evo-orange">Validée</p>
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
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <Button
                                type="button"
                                class="px-3 py-1.5 text-xs disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="
                                    shareSessionForm.processing ||
                                    Boolean(session.community_post_id) ||
                                    !props.canShareToCommunity
                                "
                                @click="openShareSessionModal(session)"
                            >
                                {{
                                    session.community_post_id
                                        ? 'Déjà partagée'
                                        : props.canShareToCommunity
                                          ? 'Partager'
                                          : 'Premium requis'
                                }}
                            </Button>
                        </div>
                    </div>
                </div>
                <p v-else class="mt-4 text-sm text-neutral-600">
                    Aucune séance effectuée pour le moment.
                </p>

                <p
                    v-if="flashSuccessMessage"
                    class="mt-4 text-sm text-emerald-700"
                >
                    {{ flashSuccessMessage }}
                </p>
                <p
                    v-if="communityErrorMessage"
                    class="mt-4 text-sm text-red-600"
                >
                    {{ communityErrorMessage }}
                </p>
            </section>
        </div>

        <div
            v-if="editingWorkoutSession"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold">
                        Modifier la séance
                    </h2>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeWorkoutSessionEditor"
                    >
                        Fermer
                    </Button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label
                            for="edit_session_name"
                            class="block font-medium"
                        >
                            Nom
                        </label>
                        <input
                            id="edit_session_name"
                            v-model="workoutSessionEditForm.name"
                            type="text"
                            class="evo-input"
                        />
                        <p
                            v-if="workoutSessionEditForm.errors.name"
                            class="text-sm text-red-600"
                        >
                            {{ workoutSessionEditForm.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="edit_session_description"
                            class="block font-medium"
                        >
                            Description
                        </label>
                        <textarea
                            id="edit_session_description"
                            v-model="workoutSessionEditForm.description"
                            rows="3"
                            class="evo-input"
                        />
                    </div>

                    <div class="space-y-3">
                        <p class="font-medium">Exercices de la séance</p>
                        <div
                            class="max-h-64 space-y-3 overflow-y-auto rounded-lg border border-neutral-200 p-3"
                        >
                            <p
                                v-if="exerciseGroups.length === 0"
                                class="text-sm text-neutral-500"
                            >
                                Aucun exercice disponible pour vos sports.
                            </p>
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
                                <label
                                    v-for="exercise in group.exercises"
                                    :key="exercise.id"
                                    class="flex items-center justify-between gap-3 rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm"
                                >
                                    <span class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            :checked="
                                                workoutSessionEditForm.exercise_ids.includes(
                                                    exercise.id,
                                                )
                                            "
                                            class="h-4 w-4 accent-evo-black"
                                            @change="
                                                toggleEditExercise(exercise.id)
                                            "
                                        />
                                        <span>{{ exercise.name }}</span>
                                    </span>
                                    <span class="text-xs text-neutral-500">
                                        {{ exercise.category_name }}
                                    </span>
                                </label>
                            </div>
                        </div>
                        <p
                            v-if="workoutSessionEditForm.errors.exercise_ids"
                            class="text-sm text-red-600"
                        >
                            {{ workoutSessionEditForm.errors.exercise_ids }}
                        </p>
                    </div>

                    <Button
                        type="button"
                        :disabled="workoutSessionEditForm.processing"
                        @click="updateWorkoutSession"
                    >
                        {{
                            workoutSessionEditForm.processing
                                ? 'Enregistrement...'
                                : 'Enregistrer'
                        }}
                    </Button>
                </div>
            </section>
        </div>

        <div
            v-if="editingExercise"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold">Modifier l'exercice</h2>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeExerciseEditor"
                    >
                        Fermer
                    </Button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label
                            for="edit_exercise_name"
                            class="block font-medium"
                        >
                            Nom
                        </label>
                        <input
                            id="edit_exercise_name"
                            v-model="customExerciseEditForm.name"
                            type="text"
                            class="evo-input"
                        />
                        <p
                            v-if="customExerciseEditForm.errors.name"
                            class="text-sm text-red-600"
                        >
                            {{ customExerciseEditForm.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="edit_exercise_sport"
                            class="block font-medium"
                        >
                            Sport
                        </label>
                        <select
                            id="edit_exercise_sport"
                            v-model="customExerciseEditForm.sport_id"
                            class="evo-input"
                        >
                            <option value="">Sélectionner un sport</option>
                            <option
                                v-for="sport in sports"
                                :key="sport.id"
                                :value="sport.id"
                            >
                                {{ sport.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="edit_exercise_category"
                            class="block font-medium"
                        >
                            Catégorie
                        </label>
                        <select
                            id="edit_exercise_category"
                            v-model="
                                customExerciseEditForm.exercise_category_id
                            "
                            class="evo-input"
                        >
                            <option value="">Sélectionner une catégorie</option>
                            <option
                                v-for="category in exerciseCategories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label
                            for="edit_exercise_description"
                            class="block font-medium"
                        >
                            Description
                        </label>
                        <textarea
                            id="edit_exercise_description"
                            v-model="customExerciseEditForm.description"
                            rows="3"
                            class="evo-input"
                        />
                    </div>

                    <Button
                        type="button"
                        :disabled="customExerciseEditForm.processing"
                        @click="updateExercise"
                    >
                        {{
                            customExerciseEditForm.processing
                                ? 'Enregistrement...'
                                : 'Enregistrer'
                        }}
                    </Button>
                </div>
            </section>
        </div>

        <div
            v-if="isWorkoutSessionModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold">Créer une séance</h2>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeWorkoutSessionModal"
                    >
                        Fermer
                    </Button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label for="session_name" class="block font-medium"
                            >Nom</label
                        >
                        <input
                            id="session_name"
                            v-model="workoutSessionForm.name"
                            type="text"
                            class="evo-input"
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
                            class="evo-input"
                        />
                        <p
                            v-if="workoutSessionForm.errors.description"
                            class="text-sm text-red-600"
                        >
                            {{ workoutSessionForm.errors.description }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <p class="font-medium">Exercices existants par sport</p>
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
                                        class="flex items-center justify-between gap-3 rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm hover:cursor-pointer"
                                    >
                                        <span class="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    workoutSessionForm.exercise_ids.includes(
                                                        exercise.id,
                                                    )
                                                "
                                                class="h-4 w-4 accent-evo-black"
                                                @change="
                                                    toggleExercise(exercise.id)
                                                "
                                            />
                                            <span>{{ exercise.name }}</span>
                                        </span>
                                        <span class="text-xs text-neutral-500">
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

                    <Button
                        type="button"
                        :disabled="workoutSessionForm.processing"
                        @click="createWorkoutSession"
                    >
                        {{
                            workoutSessionForm.processing
                                ? 'Création...'
                                : 'Créer la séance'
                        }}
                    </Button>
                </div>
            </section>
        </div>

        <div
            v-if="isCustomExerciseModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-lg font-semibold">
                        Créer un exercice personnalisé
                    </h2>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeCustomExerciseModal"
                    >
                        Fermer
                    </Button>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label for="exercise_name" class="block font-medium"
                            >Nom</label
                        >
                        <input
                            id="exercise_name"
                            v-model="customExerciseForm.name"
                            type="text"
                            class="evo-input"
                        />
                        <p
                            v-if="customExerciseForm.errors.name"
                            class="text-sm text-red-600"
                        >
                            {{ customExerciseForm.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="exercise_sport" class="block font-medium"
                            >Sport</label
                        >
                        <select
                            id="exercise_sport"
                            v-model="customExerciseForm.sport_id"
                            class="evo-input"
                        >
                            <option value="">Sélectionner un sport</option>
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
                        <label for="exercise_category" class="block font-medium"
                            >Catégorie</label
                        >
                        <select
                            id="exercise_category"
                            v-model="customExerciseForm.exercise_category_id"
                            class="evo-input"
                        >
                            <option value="">Sélectionner une catégorie</option>
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
                                customExerciseForm.errors.exercise_category_id
                            "
                            class="text-sm text-red-600"
                        >
                            {{ customExerciseForm.errors.exercise_category_id }}
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
                            class="evo-input"
                        />
                    </div>

                    <Button
                        type="button"
                        :disabled="customExerciseForm.processing"
                        @click="createCustomExercise"
                    >
                        {{
                            customExerciseForm.processing
                                ? 'Création...'
                                : "Créer l'exercice"
                        }}
                    </Button>
                </div>
            </section>
        </div>

        <div
            v-if="selectedShareSession"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-neutral-500">
                            Publication communautaire
                        </p>
                        <h2 class="mt-1 text-lg font-semibold">
                            {{
                                selectedShareSession.workout_session_name ??
                                'Séance'
                            }}
                        </h2>
                        <p
                            v-if="selectedShareSessionDateLabel"
                            class="mt-1 text-sm text-neutral-600"
                        >
                            Validée le {{ selectedShareSessionDateLabel }}
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeShareSessionModal"
                    >
                        Fermer
                    </Button>
                </div>

                <div
                    v-if="selectedShareSession.performances.length > 0"
                    class="mt-4 space-y-2 rounded-lg border border-neutral-200 p-3"
                >
                    <p class="text-sm font-medium">Performances partagées</p>
                    <div
                        v-for="performance in selectedShareSession.performances.slice(
                            0,
                            4,
                        )"
                        :key="performance.exercise_id"
                        class="text-sm text-neutral-700"
                    >
                        {{ formatPerformanceSummary(performance) }}
                    </div>
                    <p
                        v-if="selectedShareSession.performances.length > 4"
                        class="text-xs text-neutral-500"
                    >
                        +{{ selectedShareSession.performances.length - 4 }}
                        autre(s) performance(s)
                    </p>
                </div>

                <div class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label for="share_title" class="block font-medium">
                            Nom du post (optionnel)
                        </label>
                        <input
                            id="share_title"
                            v-model="shareSessionForm.title"
                            type="text"
                            :placeholder="
                                selectedShareSession.workout_session_name ??
                                'Séance partagée'
                            "
                            class="evo-input"
                        />
                        <p
                            v-if="shareSessionForm.errors.title"
                            class="text-sm text-red-600"
                        >
                            {{ shareSessionForm.errors.title }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label for="share_content" class="block font-medium">
                            Note (optionnel)
                        </label>
                        <textarea
                            id="share_content"
                            v-model="shareSessionForm.content"
                            rows="3"
                            class="evo-input"
                        />
                        <p
                            v-if="shareSessionForm.errors.content"
                            class="text-sm text-red-600"
                        >
                            {{ shareSessionForm.errors.content }}
                        </p>
                    </div>

                    <p
                        v-if="communityErrorMessage"
                        class="text-sm text-red-600"
                    >
                        {{ communityErrorMessage }}
                    </p>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            type="button"
                            :disabled="shareSessionForm.processing"
                            @click="sharePerformedSession"
                        >
                            {{
                                shareSessionForm.processing
                                    ? 'Partage...'
                                    : 'Partager la séance'
                            }}
                        </Button>
                        <Button
                            type="button"
                            variant="transparent"
                            @click="closeShareSessionModal"
                        >
                            Annuler
                        </Button>
                    </div>
                </div>
            </section>
        </div>

        <div
            v-if="selectedPerformedSession"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <section
                class="max-h-full w-full max-w-2xl overflow-y-auto rounded-lg bg-evo-white p-4 shadow-xl"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold">Valider la séance</h2>
                        <p class="mt-1 text-sm text-neutral-600">
                            {{
                                selectedPerformedSession.workout_session_name ??
                                'Séance'
                            }}
                            <span v-if="selectedPerformedSessionDateLabel">
                                - {{ selectedPerformedSessionDateLabel }}
                            </span>
                        </p>
                    </div>
                    <Button
                        type="button"
                        variant="transparent"
                        class="px-3 py-1 text-sm"
                        @click="closeCompleteSessionModal"
                    >
                        Fermer
                    </Button>
                </div>

                <div
                    v-if="wantsPerformanceEntry === null"
                    class="mt-4 space-y-4"
                >
                    <p class="font-medium">
                        Voulez-vous renseigner des performances sur cette séance
                        ?
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Button
                            type="button"
                            :disabled="!selectedWorkoutSessionHasExercises"
                            :class="{
                                'cursor-not-allowed opacity-50':
                                    !selectedWorkoutSessionHasExercises,
                            }"
                            @click="wantsPerformanceEntry = true"
                        >
                            Oui, ajouter des performances
                        </Button>
                        <Button
                            type="button"
                            variant="transparent"
                            @click="completeSelectedSession"
                        >
                            Non, valider la séance
                        </Button>
                    </div>
                    <p
                        v-if="!selectedWorkoutSessionHasExercises"
                        class="text-sm text-red-600"
                    >
                        Cette séance ne contient aucun exercice. Ajoutez
                        des exercices à la séance pour pouvoir renseigner
                        des performances.
                    </p>
                </div>

                <div v-else class="mt-4 space-y-4">
                    <div class="space-y-2">
                        <label for="complete_notes" class="block font-medium">
                            Notes (optionnel)
                        </label>
                        <textarea
                            id="complete_notes"
                            v-model="completeSessionForm.notes"
                            rows="3"
                            class="evo-input"
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
                            class="rounded-lg border border-neutral-200 bg-white p-4"
                        >
                            <p class="font-semibold">
                                {{
                                    selectedWorkoutSession?.exercises[
                                        exerciseIndex
                                    ]?.name ?? 'Exercice'
                                }}
                            </p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div
                                    v-for="metric in getExerciseMetrics(
                                        performance.exercise_id,
                                    )"
                                    :key="metric.key"
                                    class="space-y-1"
                                >
                                    <label class="text-sm font-medium">
                                        {{ metric.label }}
                                        <span v-if="metric.unit"
                                            >({{ metric.unit }})</span
                                        >
                                        <span
                                            v-if="metric.is_required"
                                            class="text-red-600"
                                            >*</span
                                        >
                                    </label>
                                    <input
                                        v-model="
                                            performance.metrics[metric.key]
                                        "
                                        type="number"
                                        min="0"
                                        :step="metricInputStep(metric)"
                                        class="evo-input px-3"
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
                        <Button
                            type="button"
                            :disabled="completeSessionForm.processing"
                            @click="completeSelectedSession"
                        >
                            {{
                                completeSessionForm.processing
                                    ? 'Validation...'
                                    : 'Valider la séance'
                            }}
                        </Button>
                        <Button
                            v-if="wantsPerformanceEntry"
                            type="button"
                            variant="transparent"
                            @click="wantsPerformanceEntry = false"
                        >
                            Valider sans performances
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.evolyx-calendar.vuecal) {
    border: 0;
    box-shadow: none;
    background: #fbfaf7;
    color: #111111;
}

:deep(.evolyx-calendar .vuecal__menu) {
    justify-content: center;
    gap: 0.35rem;
    border-bottom: 1px solid #e5e5e5;
    background: #e9e9e9;
    padding: 0.55rem 0.65rem 0;
}

:deep(.evolyx-calendar .vuecal__view-btn) {
    height: 2.35rem;
    border: 0;
    border-bottom: 3px solid transparent;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0 0.8rem;
    color: #5f5f5f;
    font-size: 0.85rem;
    font-weight: 700;
    transition:
        color 0.2s ease,
        background-color 0.2s ease,
        border-color 0.2s ease;
}

:deep(.evolyx-calendar .vuecal__view-btn:hover) {
    background: #f2f2f2;
    color: #111111;
}

:deep(.evolyx-calendar .vuecal__view-btn--active) {
    border-bottom-color: #ff6b00;
    background: transparent;
    color: #111111;
}

:deep(.evolyx-calendar .vuecal__title-bar) {
    min-height: 3.25rem;
    border-bottom: 1px solid #e5e5e5;
    background: #ffffff;
    color: #111111;
    font-size: 1rem;
    font-weight: 800;
}

:deep(.evolyx-calendar .vuecal__title button),
:deep(.evolyx-calendar .vuecal__arrow) {
    color: #111111;
}

:deep(.evolyx-calendar .vuecal__arrow) {
    display: grid;
    min-width: 2.5rem;
    place-items: center;
    border-radius: 999px;
    transition: background-color 0.2s ease;
}

:deep(.evolyx-calendar .vuecal__arrow:hover) {
    background: #f2f2f2;
}

:deep(.evolyx-calendar .vuecal__weekdays-headings) {
    border-bottom: 1px solid #e5e5e5;
    background: #ffffff;
}

:deep(.evolyx-calendar .vuecal__heading) {
    color: #737373;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: uppercase;
}

:deep(.evolyx-calendar .vuecal__cell:before) {
    border-color: #ededed;
}

:deep(.evolyx-calendar .vuecal__cell-content) {
    color: #262626;
}

:deep(.evolyx-calendar .vuecal__cell--out-of-scope .vuecal__cell-content) {
    color: #a3a3a3;
}

:deep(.evolyx-calendar .vuecal__cell--today) {
    background: #fff4eb;
}

:deep(.evolyx-calendar .vuecal__cell--selected) {
    background: #f7f0ff;
}

:deep(.evolyx-calendar .vuecal__cell--today .vuecal__cell-date) {
    display: inline-flex;
    min-width: 1.75rem;
    height: 1.75rem;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: #ff6b00;
    color: #ffffff;
    font-weight: 400;
}

:deep(.vuecal__event.evolyx-session-event) {
    cursor: pointer;
    border-radius: 0.2rem;
    box-shadow: none;
    font-weight: 400;
    line-height: 1.2;
    padding: 5px;
    font-size: 0.9rem;
}

:deep(.vuecal__event.evolyx-session-event--planned) {
    background-color: #f5edff;
    border: 1px solid #c7a8ff;
    color: #111111;
}

:deep(.vuecal__event.evolyx-session-event--completed) {
    background-color: #ffac80;
    border: 1px solid #f76618;
    color: #000000;
}

@media (max-width: 767px) {
    :deep(.evolyx-calendar .vuecal__menu) {
        justify-content: center;
        padding-inline: 0.5rem;
    }

    :deep(.evolyx-calendar .vuecal__view-btn) {
        flex: 1;
        max-width: 8rem;
        padding-inline: 0.5rem;
    }

    :deep(.evolyx-calendar .vuecal__title-bar) {
        min-height: 3rem;
        font-size: 0.95rem;
    }

    :deep(.evolyx-calendar .vuecal__arrow) {
        min-width: 2.25rem;
    }
}
</style>

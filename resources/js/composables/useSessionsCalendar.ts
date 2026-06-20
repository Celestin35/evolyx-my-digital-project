import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    toValue,
    type MaybeRefOrGetter,
} from 'vue';

type CalendarPerformedSession = {
    id: number;
    workout_session_name: string | null;
    performed_at: string | null;
    completed_at: string | null;
    notes: string | null;
};

export function useSessionsCalendar(
    performedSessions: MaybeRefOrGetter<CalendarPerformedSession[]>,
) {
    const isMobileCalendar = ref(false);
    const mobileCalendarMediaQuery = '(max-width: 767px)';
    let mobileCalendarQuery: MediaQueryList | null = null;
    let onMobileCalendarChange: ((event: MediaQueryListEvent) => void) | null =
        null;

    const calendarEvents = computed(() =>
        toValue(performedSessions)
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
        isMobileCalendar.value
            ? 'sessions-calendar-mobile'
            : 'sessions-calendar',
    );

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

        mobileCalendarQuery.removeEventListener(
            'change',
            onMobileCalendarChange,
        );
    });

    return {
        calendarEvents,
        calendarActiveView,
        calendarDisabledViews,
        calendarHeight,
        calendarKey,
    };
}

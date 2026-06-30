export type CommunityPerformanceMetric = {
    key: string;
    label: string;
    unit: string | null;
    value: number;
};

export type CommunityPerformance = {
    exercise_name: string | null;
    sport_name: string | null;
    category_name: string | null;
    weight: number | null;
    repetitions: number | null;
    duration_minutes: number | null;
    distance_meters: number | null;
    metric_values: CommunityPerformanceMetric[];
};

export type CommunityPost = {
    id: number;
    author_name: string | null;
    is_own_post: boolean;
    title: string | null;
    content: string | null;
    published_at: string | null;
    performed_session: {
        id: number;
        workout_session_name: string | null;
        performed_at: string | null;
        completed_at: string | null;
        performances: CommunityPerformance[];
    };
};

export type CommunityUser = {
    id: number;
    pseudo: string | null;
    display_name: string;
    initial: string;
    avatar_color: string;
    is_following: boolean;
};

export type CommunityProfile = CommunityUser & {
    followers_count: number;
    following_count: number;
    posts_count: number;
    posts: CommunityPost[];
};

<?php

use App\Models\CommunityPost;
use App\Models\PerformedSession;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WorkoutSession;
use Inertia\Testing\AssertableInertia as Assert;

function makeCommunityUser(bool $premium = false): User
{
    $user = User::factory()->create();

    $plan = SubscriptionPlan::query()->firstOrCreate(
        ['name' => $premium ? 'Premium' : 'Free'],
        [
            'price' => $premium ? 9.99 : 0,
            'ads_enabled' => ! $premium,
            'premium_features' => $premium,
        ],
    );

    Subscription::query()->create([
        'start_date' => now()->subDay(),
        'end_date' => null,
        'is_active' => true,
        'user_id' => $user->id,
        'subscription_plan_id' => $plan->id,
    ]);

    return $user;
}

function makePerformedSessionFor(User $user, bool $completed = true): PerformedSession
{
    $workoutSession = WorkoutSession::query()->create([
        'name' => 'Legs hypertrophie',
        'description' => null,
        'user_id' => $user->id,
    ]);

    return PerformedSession::query()->create([
        'user_id' => $user->id,
        'workout_session_id' => $workoutSession->id,
        'performed_at' => now()->subDay(),
        'completed_at' => $completed ? now() : null,
        'notes' => null,
    ]);
}

function makeCommunityPostFor(User $user, ?string $title = null): CommunityPost
{
    $performedSession = makePerformedSessionFor($user);

    return CommunityPost::query()->create([
        'user_id' => $user->id,
        'performed_session_id' => $performedSession->id,
        'title' => $title,
        'published_at' => now(),
    ]);
}

test('a non premium user cannot share a performed session', function () {
    $user = makeCommunityUser();
    $performedSession = makePerformedSessionFor($user);

    $response = $this
        ->actingAs($user)
        ->post(route('community.posts.store'), [
            'performed_session_id' => $performedSession->id,
        ]);

    $response->assertRedirect(route('community'));
    $response->assertSessionHasErrors('community');
    $this->assertDatabaseMissing('community_posts', [
        'performed_session_id' => $performedSession->id,
    ]);
});

test('a premium user can share a completed performed session', function () {
    $user = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($user);

    $response = $this
        ->actingAs($user)
        ->post(route('community.posts.store'), [
            'performed_session_id' => $performedSession->id,
            'title' => 'Grosse seance jambes',
            'content' => 'Bonne progression.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('community_posts', [
        'user_id' => $user->id,
        'performed_session_id' => $performedSession->id,
        'title' => 'Grosse seance jambes',
        'content' => 'Bonne progression.',
    ]);
});

test('a premium user cannot share an uncompleted performed session', function () {
    $user = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($user, completed: false);

    $response = $this
        ->actingAs($user)
        ->post(route('community.posts.store'), [
            'performed_session_id' => $performedSession->id,
        ]);

    $response->assertSessionHasErrors('community');
    $this->assertDatabaseMissing('community_posts', [
        'performed_session_id' => $performedSession->id,
    ]);
});

test('a premium user cannot share another user performed session', function () {
    $user = makeCommunityUser(premium: true);
    $otherUser = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($otherUser);

    $response = $this
        ->actingAs($user)
        ->post(route('community.posts.store'), [
            'performed_session_id' => $performedSession->id,
        ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('community_posts', [
        'performed_session_id' => $performedSession->id,
    ]);
});

test('a premium user cannot share the same performed session twice', function () {
    $user = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($user);

    CommunityPost::query()->create([
        'user_id' => $user->id,
        'performed_session_id' => $performedSession->id,
        'published_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('community.posts.store'), [
            'performed_session_id' => $performedSession->id,
        ]);

    $response->assertSessionHasErrors('community');
    expect(CommunityPost::query()->where('performed_session_id', $performedSession->id)->count())
        ->toBe(1);
});

test('the author can delete their community post', function () {
    $user = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($user);
    $post = CommunityPost::query()->create([
        'user_id' => $user->id,
        'performed_session_id' => $performedSession->id,
        'published_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('community.posts.destroy', $post));

    $response->assertRedirect();
    $this->assertDatabaseMissing('community_posts', [
        'id' => $post->id,
    ]);
});

test('another user cannot delete a community post', function () {
    $author = makeCommunityUser(premium: true);
    $otherUser = makeCommunityUser(premium: true);
    $performedSession = makePerformedSessionFor($author);
    $post = CommunityPost::query()->create([
        'user_id' => $author->id,
        'performed_session_id' => $performedSession->id,
        'published_at' => now(),
    ]);

    $response = $this
        ->actingAs($otherUser)
        ->delete(route('community.posts.destroy', $post));

    $response->assertForbidden();
    $this->assertDatabaseHas('community_posts', [
        'id' => $post->id,
    ]);
});

test('community page feed contains only followed users posts', function () {
    $user = makeCommunityUser(premium: true);
    $followedUser = makeCommunityUser(premium: true);
    $unfollowedUser = makeCommunityUser(premium: true);
    $followerUser = makeCommunityUser(premium: true);

    $user->following()->attach($followedUser->id);
    $followerUser->following()->attach($user->id);

    $followedPost = makeCommunityPostFor($followedUser, 'Post suivi');
    makeCommunityPostFor($unfollowedUser, 'Post non suivi');
    $ownPost = makeCommunityPostFor($user, 'Mon post');

    $response = $this
        ->actingAs($user)
        ->get(route('community'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Community')
        ->has('followingFeed', 1)
        ->where('followingFeed.0.id', $followedPost->id)
        ->has('ownPosts', 1)
        ->where('ownPosts.0.id', $ownPost->id)
        ->has('following', 1)
        ->where('following.0.id', $followedUser->id)
        ->has('followers', 1)
        ->where('followers.0.id', $followerUser->id),
    );
});

test('a premium user can follow and unfollow another user', function () {
    $user = makeCommunityUser(premium: true);
    $otherUser = makeCommunityUser(premium: true);

    $this
        ->actingAs($user)
        ->post(route('community.users.follow', $otherUser))
        ->assertRedirect();

    $this->assertDatabaseHas('user_follows', [
        'follower_id' => $user->id,
        'followed_id' => $otherUser->id,
    ]);

    $this
        ->actingAs($user)
        ->delete(route('community.users.unfollow', $otherUser))
        ->assertRedirect();

    $this->assertDatabaseMissing('user_follows', [
        'follower_id' => $user->id,
        'followed_id' => $otherUser->id,
    ]);
});

test('community user search returns matching users with follow state', function () {
    $user = makeCommunityUser(premium: true);
    $otherUser = makeCommunityUser(premium: true);
    $otherUser->forceFill(['pseudo' => 'maya_fit'])->save();
    $user->following()->attach($otherUser->id);

    $response = $this
        ->actingAs($user)
        ->getJson(route('community.users.search', ['q' => 'maya']));

    $response->assertOk();
    $response->assertJsonPath('users.0.id', $otherUser->id);
    $response->assertJsonPath('users.0.is_following', true);
});

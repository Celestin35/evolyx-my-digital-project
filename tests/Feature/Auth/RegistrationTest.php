<?php

use Database\Seeders\Base\RolesSeeder;
use Database\Seeders\Base\SubscriptionPlansSeeder;
use Database\Seeders\Sports\SportsSeeder;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());

    $this->seed([
        RolesSeeder::class,
        SportsSeeder::class,
        SubscriptionPlansSeeder::class,
    ]);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Test',
        'pseudo' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'sex' => 'other',
        'height' => 175,
        'weight' => 72.5,
        'activity_level' => 'moderate',
        'birth_date' => '2000-01-01',
        'sport_ids' => [1],
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration account step can be validated before profile step', function () {
    $response = $this->postJson(route('register.validate-account'), [
        'first_name' => 'Test',
        'pseudo' => 'stepuser',
        'email' => 'step@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

test('users must be at least fifteen years old to register', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Young',
        'pseudo' => 'younguser',
        'email' => 'young@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'sex' => 'other',
        'height' => 170,
        'weight' => 65,
        'activity_level' => 'moderate',
        'birth_date' => now()->subYears(15)->addDay()->toDateString(),
        'sport_ids' => [1],
    ]);

    $response->assertSessionHasErrors('birth_date');
    $this->assertGuest();
});

test('account step rejects duplicate email and pseudo', function () {
    $this->post(route('register.store'), [
        'first_name' => 'Taken',
        'pseudo' => 'takenuser',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'sex' => 'other',
        'height' => 175,
        'weight' => 72.5,
        'activity_level' => 'moderate',
        'birth_date' => '2000-01-01',
        'sport_ids' => [],
    ]);

    auth()->logout();

    $response = $this->postJson(route('register.validate-account'), [
        'first_name' => 'Other',
        'pseudo' => 'takenuser',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pseudo', 'email'])
        ->assertJsonPath('errors.pseudo.0', 'Ce pseudo est déjà utilisé.')
        ->assertJsonPath('errors.email.0', 'Un compte existe déjà avec cet email.');
});

test('account step rejects invalid pseudo and email formats', function () {
    $response = $this->postJson(route('register.validate-account'), [
        'first_name' => 'Test',
        'pseudo' => 'bad pseudo!',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['pseudo', 'email'])
        ->assertJsonPath('errors.pseudo.0', 'Le pseudo ne peut contenir que des lettres, des chiffres et des underscores.')
        ->assertJsonPath('errors.email.0', 'L\'email doit être une adresse valide.');
});

test('users can register without selecting a sport', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'NoSport',
        'pseudo' => 'nosportuser',
        'email' => 'nosport@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'sex' => 'other',
        'height' => 175,
        'weight' => 72.5,
        'activity_level' => 'moderate',
        'birth_date' => '2000-01-01',
        'sport_ids' => [],
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration rejects invalid profile values', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'Invalid',
        'pseudo' => 'invalidprofile',
        'email' => 'invalidprofile@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'sex' => 'invalid',
        'height' => 500,
        'weight' => 700,
        'activity_level' => 'invalid',
        'birth_date' => '2000-01-01',
        'sport_ids' => [999],
    ]);

    $response->assertSessionHasErrors(['sex', 'height', 'weight', 'activity_level', 'sport_ids.0']);
    $this->assertGuest();
});

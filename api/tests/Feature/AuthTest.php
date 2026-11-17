<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('registers a customer and returns a token', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'name']]);
});

it('rejects a wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('secret123')]);
    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertUnprocessable();
});

it('logs a customer in and returns a token', function () {
    $user = User::factory()->create(['password' => 'secret123']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name']]);
});

it('never exposes the password hash', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated();

    $user = User::where('email', 'salim@example.com')->firstOrFail();

    expect($response->json('user'))->not->toHaveKey('password')
        ->and($response->getContent())->not->toContain($user->password);
});

it('hashes the password it stores', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated();

    $user = User::where('email', 'salim@example.com')->firstOrFail();

    expect($user->password)->not->toBe('secret123')
        ->and(Hash::check('secret123', $user->password))->toBeTrue();
});

it('rejects a duplicate email and phone', function () {
    User::factory()->create(['email' => 'salim@example.com', 'phone' => '+96890000000']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'phone']);
});

it('validates required registration fields', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
});

it('logs out the authenticated customer and revokes the token', function () {
    $token = $this->postJson('/api/v1/auth/register', [
        'name' => 'Salim', 'email' => 'salim@example.com',
        'phone' => '+96890000000', 'password' => 'secret123',
    ])->assertCreated()->json('token');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonPath('email', 'salim@example.com');

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect(User::where('email', 'salim@example.com')->firstOrFail()->tokens()->count())->toBe(0);

    // The HTTP kernel keeps resolved guards between test requests; drop them so
    // the final request resolves the token from the database like a real request.
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user')
        ->assertUnauthorized();
});

it('requires authentication to log out', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});

it('returns a json 401 for unauthenticated api requests without an accept header', function () {
    $this->get('/api/v1/user')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Unauthenticated.');
});

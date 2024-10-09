<?php

use App\Models\User;

test('user can successful register', function () {
    $name = fake()->name();
    $email = fake()->email();
    $password = fake()->password();

    $response = $this->post('api/register', [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'c_password' => $password,
    ]);

    $response->assertCreated();
});

test('user cannot register with invalid inputs', function () {
    $this->post('api/register', [
        'name' => null, //required
        'email' => fake()->word(), //invalid format
        'password' => null, //required
        'c_password' => fake()->password(), //not equals password
    ])->assertSessionHasErrors([
        'name',
        'email',
        'password',
        'c_password',
    ]);
});

test('user can successful login', function () {
    $password = fake()->password();
    $user = User::factory()->create([
        'password' => $password,
    ]);

    $response = $this->post('api/login', [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertOk();
    $this->isAuthenticated();
});

test('user cannot login with wrong credetials', function () {
    $password = fake()->password();
    $user = User::factory()->create([
        'password' => $password,
    ]);

    $response = $this->post('api/login', [
        'email' => $user->email,
        'password' => fake()->password(),

    ]);

    $response->assertUnauthorized();
});

test('user can successful logout', function () {
    $this->login();

    $this->post('api/logout')->assertOk();
});

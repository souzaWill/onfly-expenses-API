<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use WithFaker;

    public function test_user_can_successful_register(): void
    {
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
    }

    public function test_user_cannot_register_with_invalid_inputs(): void
    {
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

    }

    public function test_user_can_successful_login(): void
    {
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
    }

    public function test_user_cannot_login_with_wrong_credetials(): void
    {
        $password = fake()->password();
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $response = $this->post('api/login', [
            'email' => $user->email,
            'password' => fake()->password(),

        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_successful_logout(): void
    {
        $this->login();

        $this->post('api/logout')->assertOk();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    private string $baseUrl = 'api/expenses';

    public function test_can_list_expenses_logged_user(): void
    {
        $user = $this->login();

        Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->get($this->baseUrl)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJson(fn (AssertableJson $json) => $json
                ->has('data.0', fn (AssertableJson $json) => $json
                    ->hasAll([
                        'id',
                        'description',
                        'value',
                        'user',
                    ])
                )
            );

    }

    public function test_cannot_list_expenses_from_other_user(): void
    {
        $this->login();

        Expense::factory()->create();

        $this->get($this->baseUrl)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_can_create_expense(): void
    {
        $user = $this->login();

        $body = [
            'description' => fake()->word(),
            'user_id' => $user->id,
            'value' => fake()->randomFloat(2, 0, 99999999.99),
        ];

        $this->post($this->baseUrl, $body)
            ->assertCreated();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => $body['description'],
            'value' => $body['value'],
        ]);
    }

    public function test_cannot_create_expense_with_invalid_inputs(): void
    {
        $user = $this->login();

        $body = [
            'description' => str()->random(192),
            'user_id' => User::count() + 2,
            'value' => fake()->randomFloat(2, 0, 99999999.99),
        ];

        $this->post($this->baseUrl, $body)
            ->assertSessionHasErrors(['user_id', 'description']);

        $this->assertDatabaseMissing('expenses', [
            'user_id' => $user->id,
        ]);
    }

    public function test_can_show_expense(): void
    {
        $user = $this->login();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $url = $this->baseUrl.'/'.$expense->id;

        $this->get($url)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $expense->id,
                    'description' => $expense->description,
                    'value' => $expense->value,
                ],
            ]);
    }

    public function test_cannot_show_expense_from_other_user(): void
    {
        $this->login();

        $expense = Expense::factory()->create();

        $url = $this->baseUrl.'/'.$expense->id;

        $this->get($url)
            ->assertForbidden();

    }

    public function test_can_update_expense(): void
    {
        $user = $this->login();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $url = $this->baseUrl.'/'.$expense->id;

        $response = $this->put($url, [
            'description' => 'updated description',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'updated description',
            'value' => $expense->value,
        ]);

    }


    public function test_cannot_update_expense_with_invalid_inputs(): void
    {
        $user = $this->login();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);
        $url = $this->baseUrl.'/'.$expense->id;

        $invalidId = User::count() + 2;
        $body = [
            'description' => str()->random(192),
            'user_id' => $invalidId,
            'value' => fake()->randomFloat(2, 0, 99999999.99),
        ];

        $this->put($url, $body)
            ->assertSessionHasErrors(['user_id', 'description']);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
        ]);

        $this->assertDatabaseMissing('expenses', [
            'user_id' => $invalidId,
            'description'=> $body['description'],
            'value' => $body['value'],
        ]);
    }

    public function test_cannot_update_expense_from_other_user(): void
    {
        $this->login();

        $expense = Expense::factory()->create();

        $url = $this->baseUrl.'/'.$expense->id;

        $response = $this->put($url, [
            'description' => 'updated description',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'user_id' => $expense->user_id,
            'description' => $expense->description,
            'value' => $expense->value,
        ]);

    }

    public function test_can_delete_expense(): void
    {
        $user = $this->login();

        $expense = Expense::factory()->create([
            'user_id' => $user->id,
        ]);

        $url = $this->baseUrl.'/'.$expense->id;

        $response = $this->delete($url);

        $response->assertNoContent();

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_cannot_delete_expense_from_other_user(): void
    {
        $this->login();

        $expense = Expense::factory()->create();

        $url = $this->baseUrl.'/'.$expense->id;

        $response = $this->delete($url);

        $response->assertForbidden();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'user_id' => $expense->user_id,
            'description' => $expense->description,
            'value' => $expense->value,
        ]);
    }

    public function test_cannot_delete_inexistent_expense(): void
    {
        $this->login();

        $invalidId = Expense::count() + 2;

        $url = $this->baseUrl.'/'.$invalidId;

        $response = $this->delete($url);

        $response->assertNotFound();

    }
}

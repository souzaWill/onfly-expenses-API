<?php

use App\Models\Expense;
use App\Models\User;
use App\Notifications\ExpenseCreated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;

beforeEach(function () {
    $this->baseUrl = 'api/expenses';
});

test('can list expenses logged user', function () {
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
                    'date',
                    'value',
                    'user',
                ])
            )
        );
});

test('cannot list expenses from other user', function () {
    $this->login();

    Expense::factory()->create();

    $this->get($this->baseUrl)
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('can create expense', function () {
    Notification::fake();

    $user = $this->login();

    $body = [
        'description' => fake()->word(),
        'date' => now()->format('Y-m-d'),
        'user_id' => $user->id,
        'value' => fake()->randomFloat(2, 0.01, 999999.99),
    ];

    $this->post($this->baseUrl, $body)
        ->assertCreated();

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'date' => $body['date'],
        'description' => $body['description'],
        'value' => $body['value'],
    ]);

    Notification::assertSentTo(
        [$user],
        ExpenseCreated::class
    );
});

test('can create expense with date in past', function () {
    Notification::fake();

    $user = $this->login();

    $body = [
        'description' => fake()->word(),
        'date' => now()->subDay()->format('Y-m-d'),
        'user_id' => $user->id,
        'value' => fake()->randomFloat(2, 0.01, 999999.99),
    ];

    $this->post($this->baseUrl, $body)
        ->assertCreated();

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'date' => $body['date'],
        'description' => $body['description'],
        'value' => $body['value'],
    ]);

    Notification::assertSentTo(
        [$user],
        ExpenseCreated::class
    );
});

test('cannot create expense with value zero', function () {
    Notification::fake();

    $user = $this->login();

    $body = [
        'description' => fake()->word(),
        'date' => now()->format('Y-m-d'),
        'user_id' => $user->id,
        'value' => 0,
    ];

    $this->post($this->baseUrl, $body)
        ->assertSessionHasErrors(['value']);

    $this->assertDatabaseMissing('expenses', [
        'user_id' => $user->id,
    ]);

    Notification::assertNotSentTo(
        [$user],
        ExpenseCreated::class
    );
});

test('cannot create expense with invalid inputs', function () {
    Notification::fake();

    $user = $this->login();

    $body = [
        'description' => str()->random(192),
        'date' => now()->addDay()->format('Y-m-d'),
        'user_id' => User::count() + 2,
        'value' => fake()->randomFloat(2, 0.01, 999999.99) * -1,
    ];

    $this->post($this->baseUrl, $body)
        ->assertSessionHasErrors(['user_id', 'description', 'date', 'value']);

    $this->assertDatabaseMissing('expenses', [
        'user_id' => $user->id,
    ]);

    Notification::assertNotSentTo(
        [$user],
        ExpenseCreated::class
    );
});

test('can show expense', function () {
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
                'date' => $expense->date,
                'description' => $expense->description,
                'value' => number_format($expense->value, 2, ',', '.'),
            ],
        ]);
});

test('cannot show expense from other user', function () {
    $this->login();

    $expense = Expense::factory()->create();

    $url = $this->baseUrl.'/'.$expense->id;

    $this->get($url)
        ->assertForbidden();
});

test('can update expense', function () {
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
        'date' => $expense->date,
        'description' => 'updated description',
        'value' => $expense->value,
    ]);
});

test('can update expense with date in past', function () {
    $user = $this->login();

    $expense = Expense::factory()->create([
        'user_id' => $user->id,
    ]);

    $url = $this->baseUrl.'/'.$expense->id;
    $dateInPast = now()->subDay()->format('Y-m-d');
    $response = $this->put($url, [
        'date' => $dateInPast,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'date' => $dateInPast,
        'description' => $expense->description,
        'value' => $expense->value,
    ]);
});

test('cannot update expense with value zero', function () {
    $user = $this->login();

    $expense = Expense::factory()->create([
        'user_id' => $user->id,
    ]);

    $url = $this->baseUrl.'/'.$expense->id;

    $this->put($url, [
        'value' => 0,
    ])->assertSessionHasErrors(['value']);

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
    ]);
});

test('cannot update expense with invalid inputs', function () {
    $user = $this->login();

    $expense = Expense::factory()->create([
        'user_id' => $user->id,
    ]);
    $url = $this->baseUrl.'/'.$expense->id;

    $invalidId = User::count() + 2;
    $body = [
        'description' => str()->random(192),
        'date' => now()->addDay(),
        'user_id' => $invalidId,
        'value' => fake()->randomFloat(2, 0.01, 999999.99) * -1,
    ];

    $this->put($url, $body)
        ->assertSessionHasErrors(['user_id', 'description', 'date', 'value']);

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
    ]);

    $this->assertDatabaseMissing('expenses', [
        'user_id' => $invalidId,
        'date' => $body['date'],
        'description' => $body['description'],
        'value' => $body['value'],
    ]);
});

test('cannot update expense from other user', function () {
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
        'date' => $expense->date,
        'description' => $expense->description,
        'value' => $expense->value,
    ]);
});

test('can delete expense', function () {
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
});

test('cannot delete expense from other user', function () {
    $this->login();

    $expense = Expense::factory()->create();

    $url = $this->baseUrl.'/'.$expense->id;

    $response = $this->delete($url);

    $response->assertForbidden();

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'user_id' => $expense->user_id,
        'date' => $expense->date,
        'description' => $expense->description,
        'value' => $expense->value,
    ]);
});

test('cannot delete inexistent expense', function () {
    $this->login();

    $invalidId = Expense::count() + 2;

    $url = $this->baseUrl.'/'.$invalidId;

    $response = $this->delete($url);

    $response->assertNotFound();
});

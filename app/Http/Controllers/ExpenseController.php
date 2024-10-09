<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Notifications\ExpenseCreated;
use Illuminate\Support\Facades\Gate;

/**
 * @group Endpoints
 * @subgroup Expenses
 */
class ExpenseController extends Controller
{
    /**
     * Listar Despesas
     * 
     * Retorna a listagem de todas as despesas acessíveis pelo usuário autenticado.
     * 
     * @response 200 {
     *    "data": [
     *        {
     *            "id": 1,
     *            "description": "Aluguel",
     *            "value": "1.500,00",
     *            "date": "2024-10-09",
     *            "user": {
     *                "id": 1,
     *                "name": "John Doe",
     *                "email": "johndoe@example.com"
     *            }
     *        },
     *        {
     *            "id": 2,
     *            "description": "Conta de luz",
     *            "value": "120,50",
     *            "date": "2024-10-08",
     *            "user": {
     *                "id": 2,
     *                "name": "Jane Doe",
     *                "email": "janedoe@example.com"
     *            }
     *        }
     *    ]
     * }
     */
    public function index()
    {
        $expense = Expense::accessibleByUser()
            ->get();

        return ExpenseResource::collection($expense)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Cadastrar Despesa
     * 
     * Cria uma nova despesa para o usuário autenticado e envia uma notificação de email ao usuário.
     * 
     * 
     * @bodyParam description string required Descrição da despesa. Exemplo: Aluguel
     * @bodyParam value float required Valor da despesa. Exemplo: 1500.00
     * @bodyParam date date required Data da despesa. Exemplo: 2024-10-09
     * 
     * @response 201 {
     *    "data": {
     *        "id": 1,
     *        "description": "Aluguel",
     *        "value": "1.500,00",
     *        "date": "2024-10-09",
     *        "user": {
     *            "id": 1,
     *            "name": "John Doe",
     *            "email": "johndoe@example.com"
     *        }
     *    }
     * }
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());

        $expense->user->notify(
            new ExpenseCreated($expense)
        );

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);

    }

    /**
     * Exibir Detalhes da Despesa
     * 
     * Retorna os detalhes de uma despesa específica se o usuário tiver permissão para visualizá-la.
     * 
     * 
     * @urlParam expense int required O ID da despesa. Exemplo: 1
     * 
     * @response 200 {
     *    "data": {
     *        "id": 1,
     *        "description": "Aluguel",
     *        "value": "1.500,00",
     *        "date": "2024-10-09",
     *        "user": {
     *            "id": 1,
     *            "name": "John Doe",
     *            "email": "johndoe@example.com"
     *        }
     *    }
     * }
     * 
     * @response 403 {
     *    "message": "This action is unauthorized."
     * }
     * 
     * @response 404 {
     *    "message": "No query results for model [Expense] 1"
     * }
     */
    public function show(Expense $expense)
    {
        Gate::authorize('view', $expense);

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Atualizar Despesa
     * 
     * Atualiza os dados de uma despesa específica.
     * 
     * @urlParam expense int required O ID da despesa que será atualizada. Exemplo: 1
     * 
     * @bodyParam description string required A nova descrição da despesa. Exemplo: Aluguel Atualizado
     * @bodyParam value float required O novo valor da despesa. Exemplo: 1600.00
     * @bodyParam date date required A nova data da despesa. Exemplo: 2024-10-09
     * 
     * @response 200 {
     *    "data": {
     *        "id": 1,
     *        "description": "Aluguel Atualizado",
     *        "value": "1.600,00",
     *        "date": "2024-10-09",
     *        "user": {
     *            "id": 1,
     *            "name": "John Doe",
     *            "email": "johndoe@example.com"
     *        }
     *    }
     * }
     * 
     * @response 403 {
     *    "message": "This action is unauthorized."
     * }
     * 
     * @response 422 {
     *    "message": "The given data was invalid.",
     *    "errors": {
     *        "description": [
     *            "The description field is required."
     *        ]
     *    }
     * }
     */
    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $expense->fill($request->validated());
        $expense->save();

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Deletar Despesa
     * 
     * Exclui uma despesa específica.
     * 
     * @urlParam expense int required O ID da despesa que será deletada. Exemplo: 1
     * 
     * @response 204 {}
     * 
     * @response 403 {
     *    "message": "This action is unauthorized."
     * }
     * 
     * @response 404 {
     *    "message": "No query results for model [Expense] 1"
     * }
     */
    public function destroy(Expense $expense)
    {
        Gate::authorize('delete', $expense);

        $expense->delete();

        return response()->noContent();
    }
}

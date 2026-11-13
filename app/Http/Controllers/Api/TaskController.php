<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Services\TaskService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(private TaskService $taskService)
    {
    }

    #[OA\Get(
        path: '/tasks',
        summary: 'Lista as tarefas do usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Tarefas'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de tarefas retornada com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
        ]
    )]
    public function index(Request $request)
    {
        $tasks = $this->taskService->getWithFilters([
            'user_id' => $request->user()->id,
        ]);

        return TaskResource::collection($tasks);
    }

    #[OA\Post(
        path: '/tasks',
        summary: 'Cadastra uma nova tarefa',
        security: [['sanctum' => []]],
        tags: ['Tarefas'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        minLength: 2,
                        maxLength: 255,
                        example: 'Estudar Flutter'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Finalizar o trabalho da faculdade'
                    ),
                    new OA\Property(
                        property: 'due_date',
                        type: 'string',
                        format: 'date',
                        nullable: true,
                        example: '2026-11-15'
                    ),
                    new OA\Property(
                        property: 'is_completed',
                        type: 'boolean',
                        example: false
                    ),
                    new OA\Property(
                        property: 'category_id',
                        type: 'integer',
                        nullable: true,
                        example: 2
                    ),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [2]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tarefa cadastrada com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 422,
                ref: '#/components/responses/ValidationError'
            ),
        ]
    )]
    public function store(TaskRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $task = $this->taskService->storeWithTags($data);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/tasks/{id}',
        summary: 'Consulta uma tarefa pelo ID',
        security: [['sanctum' => []]],
        tags: ['Tarefas'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da tarefa',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tarefa encontrada'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Acesso negado'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/responses/NotFound'
            ),
        ]
    )]
    public function show(Request $request, string $id)
    {
        $task = $this->taskService->show($id);

        abort_unless($task->user_id === $request->user()->id, 403);

        $task->load(['category', 'tags']);

        return new TaskResource($task);
    }

    #[OA\Put(
        path: '/tasks/{id}',
        summary: 'Atualiza uma tarefa',
        security: [['sanctum' => []]],
        tags: ['Tarefas'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        minLength: 2,
                        maxLength: 255,
                        example: 'Estudar Flutter - Atualizado'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Revisar o projeto'
                    ),
                    new OA\Property(
                        property: 'due_date',
                        type: 'string',
                        format: 'date',
                        nullable: true,
                        example: '2026-11-15'
                    ),
                    new OA\Property(
                        property: 'is_completed',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'category_id',
                        type: 'integer',
                        nullable: true,
                        example: 2
                    ),
                    new OA\Property(
                        property: 'tags',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [2]
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tarefa atualizada com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Acesso negado'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/responses/NotFound'
            ),
            new OA\Response(
                response: 422,
                ref: '#/components/responses/ValidationError'
            ),
        ]
    )]
    public function update(TaskRequest $request, string $id)
    {
        $task = $this->taskService->show($id);

        abort_unless($task->user_id === $request->user()->id, 403);

        $updatedTask = $this->taskService->updateWithTags(
            $request->validated(),
            $id
        );

        return new TaskResource($updatedTask);
    }

    #[OA\Delete(
        path: '/tasks/{id}',
        summary: 'Exclui uma tarefa',
        security: [['sanctum' => []]],
        tags: ['Tarefas'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Tarefa excluída com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 403,
                description: 'Acesso negado'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/responses/NotFound'
            ),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $task = $this->taskService->show($id);

        abort_unless($task->user_id === $request->user()->id, 403);

        $this->taskService->destroy($id);

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

    #[OA\Get(
        path: '/users',
        summary: 'Lista o usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Usuários'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário retornado com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
        ]
    )]
    public function index(Request $request)
    {
        return UserResource::collection(
            $request->user()->newQuery()
                ->whereKey($request->user()->getKey())
                ->get()
        );
    }

    #[OA\Post(
        path: '/users',
        summary: 'Cadastra um novo usuário',
        security: [['sanctum' => []]],
        tags: ['Usuários'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Maria Silva'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'maria@email.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'senha123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário cadastrado com sucesso'
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
    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->store(
            $request->validated()
        );

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/users/{id}',
        summary: 'Consulta o próprio usuário',
        security: [['sanctum' => []]],
        tags: ['Usuários'],
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
                response: 200,
                description: 'Usuário encontrado'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/responses/NotFound'
            ),
        ]
    )]
    public function show(Request $request, string $id)
    {
        abort_unless(
            (string) $request->user()->getKey() === $id,
            404
        );

        return new UserResource(
            $this->userService->show($id)
        );
    }

    #[OA\Put(
        path: '/users/{id}',
        summary: 'Atualiza o próprio usuário',
        security: [['sanctum' => []]],
        tags: ['Usuários'],
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
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Maria Silva'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'maria@email.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'novasenha123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário atualizado com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
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
    public function update(Request $request, string $id)
    {
        abort_unless(
            (string) $request->user()->getKey() === $id,
            404
        );

        $data = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:100',
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(
                    $request->user()->getKey()
                ),
            ],
            'password' => [
                'sometimes',
                'required',
                'string',
                'min:8',
                'max:72',
            ],
        ]);

        $user = $this->userService->update(
            $data,
            $id
        );

        return new UserResource($user);
    }

    #[OA\Delete(
        path: '/users/{id}',
        summary: 'Exclui o próprio usuário',
        security: [['sanctum' => []]],
        tags: ['Usuários'],
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
                description: 'Usuário excluído com sucesso'
            ),
            new OA\Response(
                response: 401,
                ref: '#/components/responses/Unauthenticated'
            ),
            new OA\Response(
                response: 404,
                ref: '#/components/responses/NotFound'
            ),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        abort_unless(
            (string) $request->user()->getKey() === $id,
            404
        );

        $this->userService->destroy($id);

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Services\CategoryService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    #[OA\Get(
        path: '/categories',
        summary: 'Lista as categorias do usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Categorias'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'query',
                required: false,
                description: 'Filtra categorias pelo nome',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de categorias retornada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        $categories = $this->categoryService->getWithFilters([
            'user_id' => $request->user()->id,
            'name' => $request->query('name'),
        ]);

        return CategoryResource::collection($categories);
    }

    #[OA\Post(
        path: '/categories',
        summary: 'Cadastra uma nova categoria',
        security: [['sanctum' => []]],
        tags: ['Categorias'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Faculdade'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Atividades da faculdade'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Categoria cadastrada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = $request->user()->id;

        $category = $this->categoryService->store($data);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/categories/{id}',
        summary: 'Consulta uma categoria pelo ID',
        security: [['sanctum' => []]],
        tags: ['Categorias'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categoria encontrada'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(Request $request, string $id)
    {
        $category = $this->categoryService->show($id);

        abort_if(
            $category->user_id !== $request->user()->id,
            404
        );

        return new CategoryResource($category);
    }

    #[OA\Put(
        path: '/categories/{id}',
        summary: 'Atualiza uma categoria',
        security: [['sanctum' => []]],
        tags: ['Categorias'],
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
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Faculdade'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        nullable: true,
                        example: 'Trabalhos e provas'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Categoria atualizada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function update(CategoryRequest $request, string $id)
    {
        $category = $this->categoryService->show($id);

        abort_if(
            $category->user_id !== $request->user()->id,
            404
        );

        $category = $this->categoryService->update(
            $request->validated(),
            $id
        );

        return new CategoryResource($category);
    }

    #[OA\Delete(
        path: '/categories/{id}',
        summary: 'Exclui uma categoria',
        security: [['sanctum' => []]],
        tags: ['Categorias'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Categoria excluída com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $category = $this->categoryService->show($id);

        abort_if(
            $category->user_id !== $request->user()->id,
            404
        );

        $this->categoryService->destroy($id);

        return response()->noContent();
    }
}

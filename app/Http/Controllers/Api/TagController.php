<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Http\Services\TagService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    public function __construct(private TagService $tagService)
    {
    }

    #[OA\Get(
        path: '/tags',
        summary: 'Lista as etiquetas do usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Etiquetas'],
        parameters: [
            new OA\Parameter(
                name: 'name',
                in: 'query',
                required: false,
                description: 'Filtra etiquetas pelo nome',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista de etiquetas retornada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
        ]
    )]
    public function index(Request $request)
    {
        $tags = $this->tagService->getWithFilters([
            'user_id' => $request->user()->id,
            'name' => $request->query('name'),
        ]);

        return TagResource::collection($tags);
    }

    #[OA\Post(
        path: '/tags',
        summary: 'Cadastra uma nova etiqueta',
        security: [['sanctum' => []]],
        tags: ['Etiquetas'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Urgente'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Etiqueta cadastrada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function store(TagRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $tag = $this->tagService->store($data);

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/tags/{id}',
        summary: 'Consulta uma etiqueta pelo ID',
        security: [['sanctum' => []]],
        tags: ['Etiquetas'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Etiqueta encontrada'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function show(Request $request, string $id)
    {
        $tag = $this->tagService->show($id);

        abort_if($tag->user_id !== $request->user()->id, 404);

        return new TagResource($tag);
    }

    #[OA\Put(
        path: '/tags/{id}',
        summary: 'Atualiza uma etiqueta',
        security: [['sanctum' => []]],
        tags: ['Etiquetas'],
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
                        example: 'Muito urgente'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Etiqueta atualizada com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function update(TagRequest $request, string $id)
    {
        $tag = $this->tagService->show($id);

        abort_if($tag->user_id !== $request->user()->id, 404);

        $tag = $this->tagService->update(
            $request->validated(),
            $id
        );

        return new TagResource($tag);
    }

    #[OA\Delete(
        path: '/tags/{id}',
        summary: 'Exclui uma etiqueta',
        security: [['sanctum' => []]],
        tags: ['Etiquetas'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Etiqueta excluída com sucesso'),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthenticated'),
            new OA\Response(response: 404, ref: '#/components/responses/NotFound'),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $tag = $this->tagService->show($id);

        abort_if($tag->user_id !== $request->user()->id, 404);

        $this->tagService->destroy($id);

        return response()->noContent();
    }
}

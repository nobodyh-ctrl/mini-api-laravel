<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJugadorRequest;
use App\Http\Resources\JugadorResource;
use App\Models\Jugador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class JugadorController extends Controller
{
    #[OA\Get(
        path: '/api/jugadores',
        summary: 'Listar los jugadores del club',
        tags: ['Jugadores'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de jugadores',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Jugador')),
                    ]
                )
            ),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        return JugadorResource::collection(Jugador::orderBy('numero_camiseta')->get());
    }

    #[OA\Get(
        path: '/api/jugadores/{id}',
        summary: 'Obtener un jugador por ID',
        tags: ['Jugadores'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jugador encontrado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Jugador'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Jugador no encontrado'),
        ]
    )]
    public function show(Jugador $jugador): JugadorResource
    {
        return new JugadorResource($jugador);
    }

    #[OA\Post(
        path: '/api/jugadores',
        summary: 'Crear un jugador',
        tags: ['Jugadores'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/JugadorInput')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Jugador creado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Jugador'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Datos inválidos'),
        ]
    )]
    public function store(StoreJugadorRequest $request): JsonResponse
    {
        $jugador = Jugador::create($request->validated());

        return (new JugadorResource($jugador))->response()->setStatusCode(201);
    }
}

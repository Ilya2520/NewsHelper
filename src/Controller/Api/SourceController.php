<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\SourceService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use InvalidArgumentException;

#[Route('/source')]
class SourceController
{
    private SourceService $sourceService;
    
    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }
    
    #[Route('/', name: 'source_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/source/",
        description: "Get list of all sources",
        summary: "Get all sources",
        tags: ["Источники"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of sources",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "name", type: "string"),
                            new OA\Property(property: "rssUrl", type: "string")
                        ]
                    )
                )
            )
        ]
    )]
    public function getAllSources(): JsonResponse
    {
        $sources = $this->sourceService->getAllSources();
        
        return new JsonResponse($sources, Response::HTTP_OK);
    }
    
    #[Route('/', name: 'source_create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/source/",
        description: "Create a new source",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "BBC"),
                    new OA\Property(property: "rssUrl", type: "string", example: "https://rss.bbc.com/news")
                ]
            )
        ),
        tags: ["Источники"]
    )]
    public function createSource(Request $request): JsonResponse
    {
        $data = $request->toArray();
        
        try {
            $source = $this->sourceService->createSource($data);
            
            return $source
                ? new JsonResponse($source, Response::HTTP_CREATED)
                : new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'error' => 'An error occurred while creating the source',
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/{id}', name: 'source_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: "/api/source/{id}",
        description: "Update an existing source",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Reuters"),
                    new OA\Property(property: "rssUrl", type: "string", example: "https://rss.reuters.com/news")
                ]
            )
        ),
        tags: ["Источники"]
    )]
    public function updateSource(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $data['id'] = $id;
        
        try {
            $updatedSource = $this->sourceService->updateSource($data);
            
            return $updatedSource
                ? new JsonResponse($updatedSource, Response::HTTP_OK)
                : new JsonResponse(['error' => 'Source not found'], Response::HTTP_NOT_FOUND);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'error' => 'An error occurred while creating the source',
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/{id}', name: 'source_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/source/{id}",
        description: "Deletes a source by its ID",
        summary: "Delete a source",
        tags: ["Источники"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the source",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 404, description: "Source Not Found")
        ]
    )]
    public function deleteSource(int $id): JsonResponse
    {
        try {
            $this->sourceService->deleteSource($id);
            
            return new JsonResponse(['status' => 'deleted'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'error' => 'An error occurred while creating the source',
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

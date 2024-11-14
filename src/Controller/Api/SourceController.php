<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\NewsService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/source')]
class SourceController
{
    private NewsService $newsService;
    
    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
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
        $source = $this->newsService->createSource($data);
        
        return $source
            ? new JsonResponse($source, Response::HTTP_CREATED)
            : new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
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
        $updatedSource = $this->newsService->updateSource($data);
        
        return $updatedSource
            ? new JsonResponse($updatedSource, Response::HTTP_OK)
            : new JsonResponse(['error' => 'Source not found'], Response::HTTP_NOT_FOUND);
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
        $this->newsService->deleteSource($id);
        
        return new JsonResponse(['status' => 'deleted']);
    }
}
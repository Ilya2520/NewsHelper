<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Storage\NewsStorage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/news')]
class NewsController
{
    private NewsStorage $newsStorage;
    
    public function __construct(NewsStorage $newsStorage)
    {
        $this->newsStorage = $newsStorage;
    }
    
    #[Route('/list', name: 'news_list', methods: 'GET')]
    #[OA\Get(
        path: "/api/news/list",
        description: "Returns a list of news articles based on filters",
        summary: "Get a list of news",
        tags: ["Новости"])
    ]
    #[OA\Parameter(
        name: "fromDate",
        description: "Start date for filtering news articles",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "string", format: "date", example: "2024-01-01")
    )]
    #[OA\Parameter(
        name: "toDate",
        description: "End date for filtering news articles",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "string", format: "date", example: "2024-01-01")
    )]
    #[OA\Parameter(
        name: "page",
        description: "Page number for pagination",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "integer", example: 1)
    )]
    #[OA\Parameter(
        name: "limit",
        description: "Number of news items per page",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "integer", example: 10)
    )]
    public function listNews(Request $request): JsonResponse
    {
        $fromDate = $request->query->get('fromDate', date('Y-m-d', strtotime('-7 days')));
        $toDate = $request->query->get('toDate', date('Y-m-d'));
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        
        try {
            $newsList = $this->newsStorage->getNewsList($fromDate, $toDate, $page, $limit);
            
            return (
                new JsonResponse(json_decode($newsList), Response::HTTP_OK)
            )->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    #[Route('/{id}', name: 'news_by_id', methods: ['GET'])]
    #[OA\Get(
        path: "/api/news/{id}",
        description: "Returns details of a specific news item by its ID",
        summary: "Get news by ID",
        tags: ["Новости"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the news item",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "title", type: "string"),
                    new OA\Property(property: "content", type: "string"),
                    new OA\Property(property: "category", type: "string"),
                    new OA\Property(property: "link", type: "string"),
                    new OA\Property(property: "source", type: "string"),
                    new OA\Property(property: "publishedAt", type: "string", format: "date-time"),
                ])
            ),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function viewNews(int $id): JsonResponse
    {
        $newsData = $this->newsStorage->getNewsById($id);
        
        return (new JsonResponse(json_decode($newsData)));
    }
    
    #[Route('/', name: 'news_create', methods: 'POST')]
    #[OA\Post(
        path: "/api/news/",
        description: "Creates a new news item",
        summary: "Create a news item",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Sample News Title"),
                    new OA\Property(property: "content", type: "string", example: "Content of the news..."),
                    new OA\Property(property: "publishedAt", type: "string", format: "date-time", example: "2024-11-15T14:30:00Z"),
                    new OA\Property(property: "link", type: "string", example: "https://news.example.com/article"),
                    new OA\Property(property: "category", type: "string", example: "Politics"),
                    new OA\Property(property: "sourceName", type: "string", example: "Source Name"),
                    new OA\Property(property: "sourceUrl", type: "string", example: "https://source.example.com")
                ]
            )
        ),
        tags: ["Новости"],
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 400, description: "Bad Request")
        ]
    )]
    public function createNews(Request $request): JsonResponse
    {
        $newsData = $request->request->all();
        $newNews = $this->newsStorage->createOrUpdateNews($newsData);
        
        return $newNews !== null
            ? new JsonResponse($newNews, Response::HTTP_CREATED, )
            : new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
    
    #[Route('/{id}', name: 'news_update', methods: 'PATCH')]
    #[OA\Patch(
        path: "/api/news/{id}",
        description: "Updates an existing news item by its ID",
        summary: "Update a news item",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Updated News Title"),
                    new OA\Property(property: "content", type: "string", example: "Updated content of the news..."),
                    new OA\Property(property: "publishedAt", type: "string", format: "date-time", example: "2024-11-15T14:30:00Z"),
                    new OA\Property(property: "link", type: "string", example: "https://news.example.com/updated-article"),
                    new OA\Property(property: "category", type: "string", example: "Technology"),
                    new OA\Property(property: "sourceName", type: "string", example: "Updated Source Name"),
                    new OA\Property(property: "sourceUrl", type: "string", example: "https://updated-source.example.com")
                ]
            )
        ),
        tags: ["Новости"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the news item",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 400, description: "Bad Request"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function updateNews(int $id, Request $request): JsonResponse
    {
        $newsData = array_merge(['id' => $id], $request->request->all());
        $updatedNews = $this->newsStorage->createOrUpdateNews($newsData);
        
        return $updatedNews !== null
            ? new JsonResponse($updatedNews, Response::HTTP_OK)
            : new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
    
    #[Route('/{id}', name: 'news_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/news/{id}",
        description: "Deletes a news item by its ID",
        summary: "Delete a news item",
        tags: ["Новости"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the news item",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    #[Route('/{id}', name: 'news_delete', methods: 'DELETE')]
    public function deleteNews(int $id): JsonResponse
    {
        $this->newsStorage->deleteNews($id);
        
        return new JsonResponse(['status' => 'deleted']);
    }
}
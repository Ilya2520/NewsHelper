<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\NewsService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController
{
    private NewsService $newsService;
    
    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }
    
    #[Route('/', name: 'category_create', methods: ['POST'])]
    #[OA\Post(
        path: "/api/category/",
        description: "Create a new category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Технологии")
                ]
            )
        ),
        tags: ["Категории"]
    )]
    public function createCategory(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $category = $this->newsService->createCategory($data);
        
        return $category
            ? new JsonResponse($category, Response::HTTP_CREATED)
            : new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
    }
    
    #[Route('/{id}', name: 'category_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: "/api/category/{id}",
        description: "Update an existing category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Science")
                ]
            )
        ),
        tags: ["Категории"]
    )]
    public function updateCategory(int $id, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $data['id'] = $id;
        $updatedCategory = $this->newsService->updateCategory($data);
        
        return $updatedCategory
            ? new JsonResponse($updatedCategory, Response::HTTP_OK)
            : new JsonResponse(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
    }
    
    #[Route('/{id}', name: 'category_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: "/api/category/{id}",
        description: "Deletes a category by its ID",
        summary: "Delete a category",
        tags: ["Категории"],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "ID of the category",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 404, description: "Category Not Found")
        ]
    )]
    public function deleteCategory(int $id): JsonResponse
    {
        $this->newsService->deleteCategory($id);
        
        return new JsonResponse(['status' => 'deleted']);
    }
}

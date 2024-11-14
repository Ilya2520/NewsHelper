<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\CategoryService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController
{
    private CategoryService $categoryService;
    
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    
    #[Route('/', name: 'category_list', methods: ['GET'])]
    #[OA\Get(
        path: "/api/category/",
        description: "Get list of all categories",
        summary: "Get all categories",
        tags: ["Категории"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of categories",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "name", type: "string")
                        ]
                    )
                )
            )
        ]
    )]
    public function getAllCategories(): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories();
        
        return new JsonResponse($categories, Response::HTTP_OK);
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
        try {
            $category = $this->categoryService->createCategory($data);
            return new JsonResponse($category, Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'error' => 'Unexpected error occurred',
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
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
        try {
            $updatedCategory = $this->categoryService->updateCategory($data);
            return new JsonResponse($updatedCategory, Response::HTTP_OK);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'error' => 'Unexpected error occurred',
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
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
        try {
            $this->categoryService->deleteCategory($id);
            return new JsonResponse(['status' => 'deleted'], Response::HTTP_OK);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            return new JsonResponse(
                [
                    'error' => 'Unexpected error occurred',
                    'message' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

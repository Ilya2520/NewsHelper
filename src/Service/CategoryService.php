<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class CategoryService
{
    private CategoryRepository $categoryRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }
    
    /**
     * Получить все категории.
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        $categories = $this->categoryRepository->findAll();
        $categoriesList = [];
        
        /** @var Category $categories */
        foreach ($categories as $category) {
            $categoriesList[] = $category->toArray();
        }
        
        return $categoriesList;
    }
    
    private function save(Category $category): void
    {
        try {
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to save category: ' . $e->getMessage());
        }
    }
    
    /**
     * @param string|null $categoryName
     *
     * @return Category|null
     */
    public function findOrCreateCategory(?string $categoryName): ?Category
    {
        if ($categoryName === null) {
            throw new InvalidArgumentException("Category name cannot be null");
        }
        
        $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
        
        if ($category === null) {
            $category = $this->createCategory(['name' => $categoryName]);
        }
        
        return $category;
    }
    
    /**
     * @param array $data
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return Category|null
     */
    public function createCategory(array $data): ?Category
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Category name cannot be empty");
        }
        
        $categoryName = $data['name'];
        
        if ($this->categoryRepository->findOneBy(['name' => $categoryName]) !== null) {
            throw new InvalidArgumentException("Category with this name already exists");
        }
        
        $category = (new Category())->setName($categoryName);
        $this->save($category);
        
        return $category;
    }
    
    /**
     * @param array $data
     *
     * @return Category|null
     */
    public function updateCategory(array $data): ?Category
    {
        if (empty($data['id']) || empty($data['name'])) {
            throw new InvalidArgumentException("ID and name must be provided");
        }
        
        $category = $this->categoryRepository->find($data['id']);
        
        if ($category === null) {
            throw new InvalidArgumentException("Category with ID {$data['id']} not found");
        }
        
        $category->setName($data['name']);
        $this->entityManager->flush();
        
        return $category;
    }
    
    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteCategory(int $id): void
    {
        $category = $this->categoryRepository->find($id);
        
        if ($category === null) {
            throw new InvalidArgumentException("Category with ID $id not found");
        }
        
        try {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to delete category: " . $e->getMessage());
        }
    }
}

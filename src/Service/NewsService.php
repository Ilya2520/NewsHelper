<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Entity\News;
use App\Entity\Source;
use App\Repository\CategoryRepository;
use App\Repository\NewsRepository;
use App\Repository\SourceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;

class NewsService
{
    private const CHUNK_SIZE = 150;
    private NewsRepository $newsRepository;
    private CategoryRepository $categoryRepository;
    private SourceRepository $sourceRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        NewsRepository $newsRepository,
        CategoryRepository $categoryRepository,
        SourceRepository $sourceRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->newsRepository = $newsRepository;
        $this->categoryRepository = $categoryRepository;
        $this->sourceRepository = $sourceRepository;
        $this->entityManager = $entityManager;
    }
    
    public function getNewsList($fromDate, $toDate, $page = 1, $limit = 10): string
    {
        $offset = ($page - 1) * $limit;
        $newsList = new ArrayCollection();
        
        $chunk = $this->newsRepository->getNewsByDateRange($fromDate, $toDate, self::CHUNK_SIZE, $offset);

        foreach ($chunk as $news) {
            $newsList[] = $this->toArray($news);
        }
        
        return json_encode($newsList->toArray(), JSON_UNESCAPED_UNICODE);
    }
    
    public function getNewsById(int $id): string
    {
        $news = $this->newsRepository->find($id);
        
        if (!$news) {
            throw new \InvalidArgumentException("News with ID $id not found");
        }
        
        return json_encode($this->toArray($news), JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * @param array $data
     *
     * @return News|null
     */
    public function createOrUpdateNews(array $data): ?News
    {
        $news = $this->fromArray($data);
        
        if ($news !== null) {
            $this->save($news);
            
            return $news;
        }
        
        return null;
    }
    
    private function findOrCreateCategory(?string $categoryName): ?Category
    {
        $category = $this->categoryRepository->findOneBy(['name' => $categoryName]);
        
        if ($category === null && $categoryName !== null) {
            $category = new Category();
            $category->setName($categoryName);
            
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }
        
        return $category;
    }
    
    private function findOrCreateSource(?string $sourceName, ?string $sourceUrl): ?Source
    {
        $source = $this->sourceRepository->findOneBy(['name' => $sourceName, 'rssUrl' => $sourceUrl]);
        
        if ($source === null && $sourceName !== null && $sourceUrl !== null) {
            $source = new Source();
            $source->setName($sourceName);
            $source->setRssUrl($sourceUrl);
            
            $this->entityManager->persist($source);
            $this->entityManager->flush();
        }
        
        return $source;
    }
    
    public function deleteNews(int $id): void
    {
        $news = $this->newsRepository->find($id);
        
        if ($news) {
            $this->entityManager->remove($news);
            $this->entityManager->flush();
        }
    }
    
    public function createCategory(array $data): ?Category
    {
        if (empty($data['name'])) {
            return null;
        }
        
        $category = new Category();
        $category->setName($data['name']);
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        return $category;
    }
    
    public function updateCategory(array $data): ?Category
    {
        $category = $this->categoryRepository->find($data['id']);
        if (!$category || empty($data['name'])) {
            return null;
        }
        
        $category->setName($data['name']);
        $this->entityManager->flush();
        
        return $category;
    }
    
    public function deleteCategory(int $id): void
    {
        $category = $this->categoryRepository->find($id);
        if ($category) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();
        }
    }
    
    public function createSource(array $data): ?Source
    {
        if (empty($data['name']) || empty($data['rssUrl'])) {
            return null;
        }
        
        $source = new Source();
        $source->setName($data['name']);
        $source->setRssUrl($data['rssUrl']);
        $this->entityManager->persist($source);
        $this->entityManager->flush();
        
        return $source;
    }
    
    public function updateSource(array $data): ?Source
    {
        $source = $this->sourceRepository->find($data['id']);
        if (!$source || empty($data['name']) || empty($data['rssUrl'])) {
            return null;
        }
        
        $source->setName($data['name']);
        $source->setRssUrl($data['rssUrl']);
        $this->entityManager->flush();
        
        return $source;
    }
    
    public function deleteSource(int $id): void
    {
        $source = $this->sourceRepository->find($id);
        if ($source) {
            $this->entityManager->remove($source);
            $this->entityManager->flush();
        }
    }
    
    public function save(News $news): void
    {
        $this->entityManager->persist($news);
        $this->entityManager->flush();
    }
    
    /**
     */
    public function fromArray(array $data): ?News
    {
        $news = isset($data['id']) ? $this->newsRepository->find($data['id']) : new News();
        
        !isset($data['title']) ?: $news->setTitle((string)$data['title']);
        !isset($data['content']) ?: $news->setContent((string)$data['content']);
        !isset($data['publishedAt']) ?: $news->setPublishedAt(new \DateTime($data['publishedAt']));
        
        if (!isset($data['link'])) {
            return null;
        }
        $news->setLink((string)$data['link']);
        
        $category = $this->findOrCreateCategory($data['category'] ?? null);
        if ($category === null) {
            return null;
        }
        $news->setCategory($category);
        
        $source = $this->findOrCreateSource($data['sourceName'] ?? null, $data['sourceUrl'] ?? null);
        if ($source === null) {
            return null;
        }
        $news->setSource($source);
        
        return $news;
    }
    
    private function toArray(News $news): array
    {
        return [
            'id' => $news->getId(),
            'title' => $news->getTitle(),
            'content' => $news->getContent(),
            'category' => $news->getCategory()->getName(),
            'link' => $news->getLink(),
            'source' => $news->getSource()->getName(),
            'publishedAt' => $news->getPublishedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
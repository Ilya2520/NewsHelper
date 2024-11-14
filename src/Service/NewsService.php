<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;

class NewsService
{
    private const CHUNK_SIZE = 150;
    private NewsRepository $newsRepository;
    private CategoryService $categoryService;
    private SourceService $sourceService;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        NewsRepository $newsRepository,
        CategoryService $categoryService,
        SourceService $sourceService,
        EntityManagerInterface $entityManager
    ) {
        $this->newsRepository = $newsRepository;
        $this->categoryService = $categoryService;
        $this->sourceService = $sourceService;
        $this->entityManager = $entityManager;
    }
    
    /**
     * @param $fromDate
     * @param $toDate
     * @param int $page
     * @param int $limit
     *
     * @return string
     */
    public function getNewsList($fromDate, $toDate, int $page = 1, int $limit = 10): string
    {
        $offset = ($page - 1) * $limit;
        $newsList = new ArrayCollection();
        
        $chunk = $this->newsRepository->getNewsByDateRange($fromDate, $toDate, self::CHUNK_SIZE, $offset);
        
        /** @var News $news */
        foreach ($chunk as $news) {
            $newsList[] = $news->toArray();
        }
        
        return json_encode($newsList->toArray(), JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * @param int $id
     *
     * @return string
     */
    public function getNewsById(int $id): string
    {
        $news = $this->newsRepository->find($id);
        
        if ($news === null) {
            throw new \InvalidArgumentException("News with ID $id not found");
        }
        
        return json_encode($news->toArray(), JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * @param array $data
     *
     * @return News|null
     * @throws Exception
     */
    public function createNews(array $data): ?News
    {
        $news = $this->buildNewsFromArray($data);
        
        try {
            $this->save($news);
            
            return $news;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid data provided for creating news: ' . $e->getMessage());
        }
    }
    
    /**
     * @param array $data
     *
     * @return News|null
     * @throws Exception
     */
    public function updateNews(array $data): ?News
    {
        $news = $this->newsRepository->find($data['id']);
        
        if ($news === null) {
            throw new InvalidArgumentException("News with ID {$data['id']} not found for update.");
        }
        $this->buildNewsFromArray($data, $news);
        
        try {
            $this->entityManager->flush();;
            
            return $news;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException('Invalid data provided for updating news: ' . $e->getMessage());
        }
    }
    
    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteNews(int $id): void
    {
        $news = $this->newsRepository->find($id);
        
        if ($news === null) {
            throw new InvalidArgumentException("News with ID $id not found for deletion.");
        }
        
        $this->entityManager->remove($news);
        $this->entityManager->flush();
    }
    
    /**
     * @param News $news
     *
     * @return void
     */
    public function save(News $news): void
    {
        $existingNews = $this->newsRepository->findOneBy(['link' => $news->getLink()]);
        if ($existingNews !== null) {
            throw new InvalidArgumentException("News with the same link already exists.");
        }
        
        //id не определен на данном этапе, тк нет записи в бд
        if (in_array(null, array_values(array_filter($news->toArray(), function($key) {
            return $key !== 'id';
        }, ARRAY_FILTER_USE_KEY)), true)) {
            throw new InvalidArgumentException("Missing required fields.");
        }
        
        $this->entityManager->persist($news);
        $this->entityManager->flush();
    }
    
    /**
     * Builds a News entity from the provided data.
     *
     * @param array $data
     * @param News|null $newsToUpdate
     *
     * @return News
     * @throws Exception
     */
    public function buildNewsFromArray(array $data, ?News $newsToUpdate = null): News
    {
        $news = $newsToUpdate ?? new News();
        
        if (isset($data['title'])) {
            $news->setTitle((string)$data['title']);
        }
        
        if (isset($data['content'])) {
            $news->setContent((string)$data['content']);
        }
        
        if (isset($data['publishedAt'])) {
            $publishedAt = $data['publishedAt'] instanceof \DateTime
                ? $data['publishedAt']
                : new \DateTime($data['publishedAt']);
            $news->setPublishedAt($publishedAt);
        }
        
        if (isset($data['link'])) {
            $news->setLink((string)$data['link']);
        }
        
        if ($newsToUpdate === null || isset($data['category'])) {
            $category = $this->categoryService->findOrCreateCategory($data['category'] ?? null);
            if ($category !== null) {
                $news->setCategory($category);
            }
        }
        
        if ($newsToUpdate === null || (isset($data['sourceName']) && isset($data['sourceUrl']))) {
            $source = $this->sourceService->findOrCreateSource(
                $data['sourceName'] ?? null,
                $data['sourceUrl'] ?? null
            );
            if ($source !== null) {
                $news->setSource($source);
            }
        }
        
        return $news;
    }
}

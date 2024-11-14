<?php

declare(strict_types=1);

namespace App\Storage;

use App\Entity\News;
use App\Service\NewsService;
use Exception;
use Psr\Cache\InvalidArgumentException;

class NewsStorage
{
    private NewsService $newsService;
    private NewsCacheInterface $newsCache;
    
    public function __construct(NewsService $newsService, NewsCacheInterface $newsCache)
    {
        $this->newsService = $newsService;
        $this->newsCache = $newsCache;
    }
    
    public function getNewsList(string $fromDate, string $toDate, int $page = 1, int $limit = 10): string
    {
        $cacheKey = $this->newsCache->getNewsListCacheKey($fromDate, $toDate, $page, $limit);
        
        return $this->newsCache->getFromCache($cacheKey, function () use ($fromDate, $toDate, $page, $limit) {
            return $this->newsService->getNewsList($fromDate, $toDate, $page, $limit);
        });
    }
    
    public function getNewsById(int $id): string
    {
        $cacheKey = $this->newsCache->getNewsByIdCacheKey($id);
        
        return $this->newsCache->getFromCache($cacheKey, function () use ($id) {
            return $this->newsService->getNewsById($id);
        }, $id);
        
    }
    
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function createNews(array $newsData): ?News
    {
        $news = $this->newsService->createNews($newsData);
        
        if ($news !== null) {
            $this->newsCache->invalidateCacheByTags(['news']);
            $this->newsCache->invalidateCacheById($news->getId());
        }
        
        return $news;
    }
    
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function updateNews(array $newsData): ?News
    {
        $news = $this->newsService->updateNews($newsData);
        
        if ($news !== null) {
            $this->newsCache->invalidateCacheByTags(['news']);
            $this->newsCache->invalidateCacheById($news->getId());
        }
        
        return $news;
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function deleteNews(int $id): void
    {
        $this->newsService->deleteNews($id);
        
        $this->newsCache->invalidateCacheByTags(['news']);
        $this->newsCache->invalidateCacheById($id);
    }
}

<?php

declare(strict_types=1);

namespace App\Storage;

use App\Entity\News;
use App\Service\NewsService;

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
        });
    }
    
    public function createOrUpdateNews(array $newsData): ?News
    {
        $news = $this->newsService->createOrUpdateNews($newsData);
        
        if ($news !== null) {
            $this->newsCache->invalidateCacheByPattern('news_list_*');
            $this->newsCache->invalidateCacheById($news['id']);
        }
        
        return $news;
    }
    
    public function deleteNews(int $id): void
    {
        $this->newsService->deleteNews($id);
        
        $this->newsCache->invalidateCacheByPattern('news_list_*');
        $this->newsCache->invalidateCacheById($id);
    }
}
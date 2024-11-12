<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\News;
use App\Repository\NewsRepository;
use Doctrine\Common\Collections\ArrayCollection;

class NewsService
{
    private NewsRepository $newsRepository;
    private const CHUNK_SIZE = 150;
    
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
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
    
    public function getNewsById(int $id): array
    {
        $news = $this->newsRepository->find($id);
        
        if (!$news) {
            throw new \InvalidArgumentException("News with ID $id not found");
        }
        
        return $this->toArray($news);
    }
    
    
    private function toArray(News $news): array
    {
        return [
            'id' => $news->getId(),
            'title' => $news->getTitle(),
            'content' => $news->getContent(),
            'category' => $news->getCategory()->getName(),
            'source' => $news->getSource()->getName(),
            'date' => $news->getPublishedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
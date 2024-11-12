<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Storage\NewsStorage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NewsController
{
    private NewsStorage $newsStorage;
    
    public function __construct(NewsStorage $newsStorage)
    {
        $this->newsStorage = $newsStorage;
    }
    
    public function listNews(Request $request): JsonResponse
    {
        $fromDate = $request->query->get('fromDate', date('Y-m-d', strtotime('-7 days')));
        $toDate = $request->query->get('toDate', date('Y-m-d'));
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);
        
        try {
            $newsList = $this->newsStorage->getNewsList($fromDate, $toDate, $page, $limit);
            
            return (new JsonResponse($newsList, 200))->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    
    public function viewNews(int $id): JsonResponse
    {
        $newsData = $this->newsStorage->getNewsById($id);
        
        return new JsonResponse($newsData);
    }
    
    public function convertToUnicodeResponse()
    {
    
    }
}
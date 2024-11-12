<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Contracts\Cache\ItemInterface;

interface NewsCacheInterface
{
    public function getNewsListCacheKey(string $fromDate, string $toDate, int $page, int $limit): string;
    
    public function getNewsByIdCacheKey(int $id): string;
    
    public function getFromCache(string $cacheKey, callable $callback);
    
    public function setCacheExpiration(ItemInterface $item): void;
}
<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class NewsCache implements NewsCacheInterface
{
    private CacheInterface $cache;
    
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    
    public function getNewsListCacheKey(string $fromDate, string $toDate, int $page, int $limit): string
    {
        return sprintf('news_list_%s_%s_page_%d_limit_%d', $fromDate, $toDate, $page, $limit);
    }
    
    public function getNewsByIdCacheKey(int $id): string
    {
        return sprintf('news_%d', $id);
    }
    
    public function getFromCache(string $cacheKey, callable $callback)
    {
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $this->setCacheExpiration($item);
            return $callback();
        });
    }
    
    public function setCacheExpiration(ItemInterface $item): void
    {
        $item->expiresAfter(3600);
    }
}

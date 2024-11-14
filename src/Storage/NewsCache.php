<?php

declare(strict_types=1);

namespace App\Storage;

use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\RedisTagAwareAdapter;

class NewsCache implements NewsCacheInterface
{
    private RedisTagAwareAdapter $cache;
    
    public function __construct(RedisTagAwareAdapter $cache)
    {
        $this->cache = $cache;
    }
    
    /**
     * @param string $fromDate
     * @param string $toDate
     * @param int $page
     * @param int $limit
     *
     * @return string
     */
    public function getNewsListCacheKey(string $fromDate, string $toDate, int $page, int $limit): string
    {
        return sprintf('news_list_%s_%s_page_%d_limit_%d', $fromDate, $toDate, $page, $limit);
    }
    
    public function getNewsByIdCacheKey(int $id): string
    {
        return sprintf('news_%d', $id);
    }
    
    /**
     * @param string $cacheKey
     * @param callable $callback
     * @param int|null $id
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getFromCache(string $cacheKey, callable $callback, int $id = null)
    {
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($callback, $id) {
            if ($id === null) {
                $item->tag(['news']);
            }
            $this->setCacheExpiration($item);
            return $callback();
        });
    }
    
    /**
     * @param ItemInterface $item
     *
     * @return void
     */
    public function setCacheExpiration(ItemInterface $item): void
    {
        $item->expiresAfter(3600);
    }
    
    /**
     * @throws InvalidArgumentException
     */
    public function invalidateCacheById(int $id): void
    {
        $this->cache->delete('news_' . $id);
    }
    
    /**
     * @param array $tags
     *
     * @return bool
     */
    public function invalidateCacheByTags(array $tags): bool
    {
        try {
            return $this->cache->invalidateTags($tags);
        } catch (InvalidArgumentException $e) {
            throw new \InvalidArgumentException('Invalid tags for cache invalidation.', 0, $e);
        }
    }
}

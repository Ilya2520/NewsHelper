<?php

declare(strict_types=1);

namespace App\Parser;

use App\Entity\News;
use App\Service\NewsService;
use DateTime;
use SimpleXMLElement;

abstract class AbstractNewsParser
{
    protected string $feedUrl;
    protected int $maxItems = 50;
    protected string $titleTag = 'title';
    protected string $categoryTag = 'category';
    protected string $dateTag = 'pubDate';
    protected string $sourceName;
    protected NewsService $newsService;
    
    public function __construct()
    {
    }
    
    /**
     * Метод для парсинга RSS-ленты и преобразования данных в массив сущностей News.
     *
     * @return News[]
     */
    public function parse(): array
    {
        $rssFeed = $this->fetchFeed();
        $newsItems = [];

        foreach ($rssFeed->channel->item as $item) {
            $newsItems[] = $this->mapItemToNews($item);
        }
        
        return $newsItems;
    }
    
    /**
     * Загружает RSS-ленту и возвращает её содержимое в виде объекта SimpleXMLElement.
     *
     * @return SimpleXMLElement
     */
    protected function fetchFeed(): SimpleXMLElement
    {
        $content = file_get_contents($this->feedUrl);
        
        if ($content === false) {
            throw new \RuntimeException("Unable to fetch feed from URL: $this->feedUrl");
        }
        
        return new SimpleXMLElement($content);
    }
    
    /**
     * Преобразует элемент RSS в сущность News.
     * Дочерние классы должны определить соответствие полей для конкретного формата.
     *
     * @param SimpleXMLElement $item
     * @return News
     */
    protected function mapItemToNews(SimpleXMLElement $item): News
    {
        $data = [
            'title' => $item->{$this->titleTag},
            'content' => $item->description,
            'publishedAt' => $this->parseDate((string) $item->{$this->dateTag}),
            'link' => $item->link,
            'category' => $item->{$this->categoryTag},
            'sourceName' => $this->sourceName,
            'rssUrl' => $this->feedUrl,
        ];
        
        $news = $this->newsService->fromArray($data);
        if ($news !== null) {
            $this->newsService->save($news);
        }
        
        return $news;
    }
    
    protected function parseDate(string $dateString): DateTime
    {
        return new DateTime($dateString);
    }
    
    protected function setFeedUrl(string $feedUrl): void
    {
        $this->feedUrl = $feedUrl;
    }
    
    protected function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }
    
    protected function setTitleTag(string $titleTag): void
    {
        $this->titleTag = $titleTag;
    }
    
    protected function setCategoryTag(string $categoryTag): void
    {
        $this->categoryTag = $categoryTag;
    }
    
    protected function setDateTag(string $dateTag): void
    {
        $this->dateTag = $dateTag;
    }
    
    protected function setSourceName(string $sourceName): void
    {
        $this->sourceName = $sourceName;
    }
    
    protected function setNewsService(NewsService $newsService): void
    {
        $this->newsService = $newsService;
    }
    
    
}

<?php

declare(strict_types=1);

namespace App\Parser;

use App\Entity\News;
use App\Service\ContentFetcher;
use App\Storage\NewsStorage;
use DateTime;
use Psr\Cache\InvalidArgumentException;
use SimpleXMLElement;
use Psr\Log\LoggerInterface;

/*
 * Базовый класс-парсер, свойства и методы определены, могут переопределяться в классах наследниках
 */
class BaseNewsParser
{
    protected string $rssUrl;
    protected int $maxItems = 10;
    protected string $titleTag = 'title';
    protected string $categoryTag = 'category';
    protected string $dateTag = 'pubDate';
    protected string $descriptionTag = 'description';
    protected string $linkTag = 'link';
    protected string $sourceName;
    protected NewsStorage $newsStorage;
    protected ContentFetcher $contentFetcher;
    private LoggerInterface $logger;
    
    
    public function __construct(NewsStorage $newsStorage, ContentFetcher $contentFetcher, LoggerInterface $logger)
    {
        $this->newsStorage = $newsStorage;
        $this->contentFetcher = $contentFetcher;
        $this->logger = $logger;
    }
    
    /**
     * Метод для парсинга RSS-ленты и преобразования данных в массив сущностей News.
     *
     * @return News[]
     * @throws InvalidArgumentException
     */
    public function parse(): array
    {
        $this->logger->info('Fetch news for', ['source' => $this->sourceName, 'rss' => $this->rssUrl]);
        $rssFeed = $this->fetchFeed();
        $newsItems = [];
        $successCounter = 0;
        
        foreach ($rssFeed->channel->item as $item) {
            try {
                $news = $this->mapItemToNews($item);
                if ($news !== null) {
                    $newsItems[] = $news;
                    $successCounter += 1;
                    $this->logger->info('News was added', $news->toArray());
                }
                if ($successCounter === $this->maxItems) {
                    $this->logger->info(sprintf('Finish parse, reached limit %s', $successCounter));

                    break;
                }
            } catch (\InvalidArgumentException $e) {
                $this->logger->warning("Error parsing news for item: " . $e->getMessage() . "\n");
            }
        }
        
        return $newsItems;
    }
    
    /**
     * Загружает RSS-ленту и возвращает её содержимое в виде объекта SimpleXMLElement.
     *
     * @return SimpleXMLElement
     * @throws \Exception
     */
    protected function fetchFeed(): SimpleXMLElement
    {
        $content = file_get_contents($this->rssUrl);
        
        if ($content === false) {
            throw new \RuntimeException("Unable to fetch feed from URL: $this->rssUrl");
        }
        
        return new SimpleXMLElement($content);
    }
    
    /**
     * Преобразует элемент RSS в сущность News.
     *
     * @param SimpleXMLElement $item
     *
     * @return News|null
     * @throws InvalidArgumentException
     */
    protected function mapItemToNews(SimpleXMLElement $item): ?News
    {
        $description = (string) $item->{$this->descriptionTag};

        $description = trim($description);
        
        if (empty($description) && isset($item->link)) {
            $this->logger->warning('Empty description at rss, try to get from news link');
            
            $description = $this->contentFetcher->fetchContent((string) $item->link) ?? $item->{$this->titleTag};
        }
        
        $data = [
            'title' => (string) $item->{$this->titleTag},
            'content' => $description,
            'publishedAt' => $this->parseDate((string) $item->{$this->dateTag}),
            'link' => (string) $item->{$this->linkTag},
            'category' => (string) $item->{$this->categoryTag},
            'sourceName' => $this->sourceName,
            'sourceUrl' => $this->rssUrl,
        ];
        $this->logger->info('Get news data, try to save', $data);
        
        return $this->newsStorage->createNews($data);
    }
    
    /**
     * @param string $dateString
     *
     * @return DateTime
     * @throws \Exception
     */
    protected function parseDate(string $dateString): DateTime
    {
        return new DateTime($dateString);
    }
    
    /**
     * @param string $rssUrl
     *
     * @return void
     */
    public function setRssUrl(string $rssUrl): void
    {
        $this->rssUrl = $rssUrl;
    }
    
    /**
     * @param int $maxItems
     *
     * @return void
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }
    
    /**
     * @param string $titleTag
     *
     * @return void
     */
    public function setTitleTag(string $titleTag): void
    {
        $this->titleTag = $titleTag;
    }
    
    /**
     * @param string $categoryTag
     *
     * @return void
     */
    public function setCategoryTag(string $categoryTag): void
    {
        $this->categoryTag = $categoryTag;
    }
    
    /**
     * @param string $dateTag
     *
     * @return void
     */
    public function setDateTag(string $dateTag): void
    {
        $this->dateTag = $dateTag;
    }
    
    /**
     * @param string $sourceName
     *
     * @return void
     */
    public function setSourceName(string $sourceName): void
    {
        $this->sourceName = $sourceName;
    }
    
    /**
     * @return ContentFetcher
     */
    public function getContentFetcher(): ContentFetcher
    {
        return $this->contentFetcher;
    }
    
    /**
     * @param string $descriptionTag
     *
     * @return void
     */
    public function setDescriptionTag(string $descriptionTag): void
    {
        $this->descriptionTag = $descriptionTag;
    }
    
    /**
     * @param string $linkTag
     *
     * @return void
     */
    public function setLinkTag(string $linkTag): void
    {
        $this->linkTag = $linkTag;
    }
    
}

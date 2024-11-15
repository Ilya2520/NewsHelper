<?php

declare(strict_types=1);

namespace App\Parser;

use App\Service\ContentFetcher;
use App\Storage\NewsStorage;
use Psr\Log\LoggerInterface;

class RbcParser extends BaseNewsParser
{
    protected string $rssUrlTemplate = 'https://rssexport.rbc.ru/rbcnews/news/30/full.rss';
    protected string $sourceName = 'РБК';
    
    public function __construct(NewsStorage $newsStorage, ContentFetcher $contentFetcher, LoggerInterface $logger)
    {
        parent::__construct($newsStorage, $contentFetcher, $logger);
        
        $this->setSourceName($this->sourceName);
        $this->setRssUrl(sprintf($this->rssUrlTemplate, $this->maxItems));
    }
    
}

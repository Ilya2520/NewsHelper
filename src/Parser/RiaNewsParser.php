<?php

declare(strict_types=1);

namespace App\Parser;

use App\Service\ContentFetcher;
use App\Storage\NewsStorage;
use Psr\Log\LoggerInterface;

class RiaNewsParser extends BaseNewsParser
{
    protected string $rssUrl = 'https://ria.ru/export/rss2/archive/index.xml';
    protected string $sourceName = 'РИА-Новости';
    
    public function __construct(NewsStorage $newsStorage, ContentFetcher $contentFetcher, LoggerInterface $logger)
    {
        parent::__construct($newsStorage, $contentFetcher, $logger);
        
        $this->setSourceName($this->sourceName);
        $this->setRssUrl($this->rssUrl);
    }
}

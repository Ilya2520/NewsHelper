<?php

declare(strict_types=1);

namespace App\Parser;

use App\Service\ContentFetcher;
use App\Storage\NewsStorage;
use Psr\Log\LoggerInterface;

class LentaRuParser extends BaseNewsParser
{
    protected string $rssUrl = 'https://lenta.ru/rss';
    protected string $sourceName = 'ЛентаРу';
    
    public function __construct(NewsStorage $newsStorage, ContentFetcher $contentFetcher, LoggerInterface $logger)
    {
        parent::__construct($newsStorage, $contentFetcher, $logger);
        
        $this->setSourceName($this->sourceName);
        $this->setRssUrl($this->rssUrl);
    }
}

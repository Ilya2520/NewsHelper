<?php

declare(strict_types=1);

namespace App\Parser;

use App\Service\NewsService;

class RbcParser extends AbstractNewsParser
{
    protected string $feedUrlTemplate = 'https://rssexport.rbc.ru/rbcnews/news/%s/full.rss';
    protected string $sourceName = 'РБК';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setSourceName($this->sourceName);
        $this->setFeedUrl(sprintf($this->feedUrlTemplate, $this->maxItems));
    }
}

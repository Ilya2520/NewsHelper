<?php

declare(strict_types=1);

namespace App\Parser;

class RiaNewsParser extends AbstractNewsParser
{
    protected string $feedUrl = 'https://ria.ru/export/rss2/archive/index.xml';
    protected string $sourceName = 'РИА-Новости';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setSourceName($this->sourceName);
        $this->setFeedUrl($this->feedUrl);
    }
}

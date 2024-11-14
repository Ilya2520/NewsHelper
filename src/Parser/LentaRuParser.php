<?php

declare(strict_types=1);

namespace App\Parser;

class LentaRuParser extends AbstractNewsParser
{
    protected string $feedUrl = 'https://lenta.ru/rss';
    protected string $sourceName = 'ЛентаРу';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->setSourceName($this->sourceName);
        $this->setFeedUrl($this->feedUrl);
    }
}

<?php

declare(strict_types=1);

namespace App\Parser;

class OtherParser extends AbstractNewsParser
{
    public function __construct(string $sourceName, string $feedUrl)
    {
        parent::__construct();
        
        $this->setSourceName($sourceName);
        $this->setFeedUrl($feedUrl);
    }
}
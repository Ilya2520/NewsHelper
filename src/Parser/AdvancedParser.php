<?php

declare(strict_types=1);

namespace App\Parser;

use App\Service\ContentFetcher;
use App\Storage\NewsStorage;
use Psr\Log\LoggerInterface;

class AdvancedParser extends BaseNewsParser
{
    public function __construct(NewsStorage $newsStorage, ContentFetcher $contentFetcher, LoggerInterface $logger)
    {
        parent::__construct($newsStorage, $contentFetcher, $logger);
    }
}
<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Класс ContentFetcher используется для извлечения контента веб-страницы по URL новости,
 * тк не все данные можно получить из RSS ленты.
 * На данный момент выполняет поиск мета-тега "description" в HTML-контенте страницы,
 * который содержит краткое описание страницы. В дальнейшем можно добавить новые теги, например для получения категории,
 * если не получилось спарсить через rss ленту
 */
class ContentFetcher
{
    protected string $descriptionPattern = '/<meta\s+(?:name=["\']description["\']\s+content=["\']([^"\']*)["\']|content=["\']([^"\']*)["\']\s+name=["\']description["\'])/i';
    private LoggerInterface $logger;
    
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @param string $descriptionPattern
     *
     * @return void
     */
    public function setDescriptionPattern(string $descriptionPattern): void
    {
        $this->descriptionPattern = $descriptionPattern;
    }
    
    /**
     * @param string $url
     *
     * @return string|null
     */
    public function fetchContent(string $url): ?string
    {
        $this->logger->info("Fetching content from URL: $url");
        
        $html = file_get_contents($url);
        if ($html === false) {
            
            return null;
        }
        
        return $this->getNewsDescription($html) ?? null;
    }
    
    /**
     * @param $htmlContent
     *
     * @return string|null
     */
    public function getNewsDescription($htmlContent): ?string
    {
        preg_match($this->descriptionPattern, $htmlContent, $matches);
        
        if (!empty($matches[1])) {
            return $matches[1];
        } elseif (!empty($matches[2])) {
            return $matches[2];
        }
        
        return null;
    }
}
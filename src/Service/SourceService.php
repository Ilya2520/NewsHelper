<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Source;
use App\Repository\SourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class SourceService
{
    private SourceRepository $sourceRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(
        SourceRepository $sourceRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->entityManager = $entityManager;
    }
    
    /**
     * Получить все источники.
     *
     * @return array
     */
    public function getAllSources(): array
    {
        $sources = $this->sourceRepository->findAll();
        $sourcesList = [];
        
        /** @var Source $source */
        foreach ($sources as $source) {
            $sourcesList[] = $source->toArray();
        }
        
        return $sourcesList;
    }
    
    /**
     * @param Source $source
     *
     * @return void
     */
    private function save(Source $source): void
    {
        try {
            $this->entityManager->persist($source);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException("Error saving the source: " . $e->getMessage());
        }
    }
    
    /**
     * @param Source $source
     * @param string $name
     * @param string $rssUrl
     *
     * @return void
     */
    private function initializeSource(Source $source, string $name, string $rssUrl): void
    {
        if (empty($name) || empty($rssUrl)) {
            throw new InvalidArgumentException("Both name and rssUrl must be provided.");
        }
        
        $source->setName($name);
        $source->setRssUrl($rssUrl);
    }
    
    /**
     * @param string|null $sourceName
     * @param string|null $sourceUrl
     *
     * @return Source|null
     */
    public function findOrCreateSource(?string $sourceName, ?string $sourceUrl): ?Source
    {
        if ($sourceName === null || $sourceUrl === null) {
            throw new InvalidArgumentException("Both sourceName and sourceUrl are required.");
        }
        
        $source = $this->sourceRepository->findOneBy(['name' => $sourceName, 'rssUrl' => $sourceUrl]);
        
        if ($source === null) {
            $source = new Source();
            $this->initializeSource($source, $sourceName, $sourceUrl);
            $this->save($source);
        }
        
        return $source;
    }
    
    /**
     * @param array $data
     *
     * @return Source|null
     */
    public function createSource(array $data): ?Source
    {
        if (empty($data['name']) || empty($data['rssUrl'])) {
            throw new InvalidArgumentException("Both 'name' and 'rssUrl' fields are required.");
        }
        
        $source = new Source();
        $this->initializeSource($source, $data['name'], $data['rssUrl']);
        $this->save($source);
        
        return $source;
    }
    
    /**
     * @param array $data
     *
     * @return Source|null
     */
    public function updateSource(array $data): ?Source
    {
        if (empty($data['id'])) {
            throw new InvalidArgumentException("The id field is required.");
        }
        
        $source = $this->sourceRepository->find($data['id']);
        
        if ($source === null) {
            throw new \RuntimeException("Source with ID {$data['id']} not found.");
        }
        
        if (!empty($data['name'])) {
            $source->setName($data['name']);
        }
        
        if (!empty($data['rssUrl'])) {
            $source->setRssUrl($data['rssUrl']);
        }
        
        $this->entityManager->flush();
        
        return $source;
    }
    
    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteSource(int $id): void
    {
        $source = $this->sourceRepository->find($id);
        
        if ($source === null) {
            throw new \InvalidArgumentException("Source with ID $id not found.");
        }
        
        try {
            $this->entityManager->remove($source);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException("Error deleting the source: " . $e->getMessage());
        }
    }
}

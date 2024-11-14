<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }
    
    public function getNewsByDateRange($fromDate, $toDate, $chunkSize, $offset)
    {
        return $this->createQueryBuilder('n')
            ->where('n.publishedAt >= :fromDate')
            ->andWhere('n.publishedAt <= :toDate')
            ->setParameter('fromDate', $fromDate)
            ->setParameter('toDate', $toDate)
            ->setFirstResult($offset)
            ->setMaxResults($chunkSize)
            ->getQuery()
            ->getResult();
    }
    
    public function getNewsCountsForAllSources(): array
    {
        $query = $this->createQueryBuilder('n')
            ->select('IDENTITY(n.source) as sourceId, COUNT(n.id) as newsCount')
            ->groupBy('n.source')
            ->getQuery();
        
        $results = $query->getResult();
        
        $counts = [];
        foreach ($results as $result) {
            $counts[$result['sourceId']] = $result['newsCount'];
        }
        
        return $counts;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\News;
use App\Entity\Source;
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
    
    public function countNewsBySource(Source $source): int
    {
        return $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.source = :source')
            ->setParameter('source', $source)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

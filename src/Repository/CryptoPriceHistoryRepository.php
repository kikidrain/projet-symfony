<?php

namespace App\Repository;

use App\Entity\CryptoPriceHistory;
use App\Entity\Crypto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CryptoPriceHistory>
 */
class CryptoPriceHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CryptoPriceHistory::class);
    }

    /**
     * Get price history for a crypto in a date range
     */
    public function getPriceHistory(Crypto $crypto, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->createQueryBuilder('ph')
            ->andWhere('ph.crypto = :crypto')
            ->andWhere('ph.recordedAt BETWEEN :from AND :to')
            ->setParameter('crypto', $crypto)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('ph.recordedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get latest price history for a crypto
     */
    public function getLatestPriceHistory(Crypto $crypto, int $limit = 100): array
    {
        return $this->createQueryBuilder('ph')
            ->andWhere('ph.crypto = :crypto')
            ->setParameter('crypto', $crypto)
            ->orderBy('ph.recordedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Clean old price history (keep only last 30 days)
     */
    public function cleanOldHistory(): int
    {
        $thirtyDaysAgo = new \DateTimeImmutable('-30 days');
        
        return $this->createQueryBuilder('ph')
            ->delete()
            ->andWhere('ph.recordedAt < :date')
            ->setParameter('date', $thirtyDaysAgo)
            ->getQuery()
            ->execute();
    }
}

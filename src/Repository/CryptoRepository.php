<?php

namespace App\Repository;

use App\Entity\Crypto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crypto>
 */
class CryptoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crypto::class);
    }

    /**
     * @return Crypto[] Returns an array of Crypto objects
     */
    public function findAllOrderedByName(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Crypto[] Returns an array of Crypto objects
     */
    public function findBySymbol(string $symbol): ?Crypto
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.symbol = :symbol')
            ->setParameter('symbol', $symbol)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

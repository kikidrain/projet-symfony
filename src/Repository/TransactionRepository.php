<?php

namespace App\Repository;

use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Crypto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * Find visible transactions for a user
     */
    public function findVisibleTransactionsByUser(User $user, int $limit = 50): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.isVisible = true')
            ->setParameter('user', $user)
            ->leftJoin('t.crypto', 'c')
            ->addSelect('c')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all transactions (including hidden ones) - for admin
     */
    public function findAllTransactions(int $limit = 100): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.user', 'u')
            ->leftJoin('t.crypto', 'c')
            ->leftJoin('t.adminUser', 'a')
            ->addSelect('u', 'c', 'a')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find transactions by crypto
     */
    public function findByCrypto(Crypto $crypto, int $limit = 50): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.crypto = :crypto')
            ->andWhere('t.isVisible = true')
            ->setParameter('crypto', $crypto)
            ->leftJoin('t.user', 'u')
            ->addSelect('u')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total volume for a crypto
     */
    public function getTotalVolumeForCrypto(Crypto $crypto): array
    {
        return $this->createQueryBuilder('t')
            ->select('SUM(t.amount) as totalAmount, SUM(t.totalValue) as totalValue')
            ->andWhere('t.crypto = :crypto')
            ->andWhere('t.isVisible = true')
            ->setParameter('crypto', $crypto)
            ->getQuery()
            ->getSingleResult();
    }
}

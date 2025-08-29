<?php

namespace App\Repository;

use App\Entity\Wallet;
use App\Entity\User;
use App\Entity\Crypto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Wallet>
 */
class WalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wallet::class);
    }

    /**
     * Find or create wallet for user and crypto
     */
    public function findOrCreateWallet(User $user, Crypto $crypto): Wallet
    {
        $wallet = $this->findOneBy(['user' => $user, 'crypto' => $crypto]);
        
        if (!$wallet) {
            $wallet = new Wallet();
            $wallet->setUser($user);
            $wallet->setCrypto($crypto);
            $this->getEntityManager()->persist($wallet);
            $this->getEntityManager()->flush();
        }
        
        return $wallet;
    }

    /**
     * Get total portfolio value for a user
     */
    public function getTotalPortfolioValue(User $user): float
    {
        $wallets = $this->findBy(['user' => $user]);
        $total = 0;
        
        foreach ($wallets as $wallet) {
            $total += $wallet->getCurrentValue();
        }
        
        return $total;
    }

    /**
     * Get all wallets with balance > 0
     */
    public function findActiveWallets(): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.balance > 0')
            ->leftJoin('w.user', 'u')
            ->leftJoin('w.crypto', 'c')
            ->addSelect('u', 'c')
            ->orderBy('w.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

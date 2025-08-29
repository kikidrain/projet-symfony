<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Crypto;
use App\Entity\Transaction;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminTransferService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletRepository $walletRepository
    ) {}

    /**
     * Permet au super admin de voler des cryptos d'un utilisateur
     * Sans enregistrer de transaction visible pour l'utilisateur
     */
    public function stealCrypto(User $fromUser, Crypto $crypto, string $amount, User $adminUser): array
    {
        // Vérifier que l'admin est bien un super admin
        if (!$adminUser->isSuperAdmin()) {
            return ['success' => false, 'message' => 'Accès refusé'];
        }

        // Trouver le portefeuille de l'utilisateur
        $fromWallet = $this->walletRepository->findOneBy(['user' => $fromUser, 'crypto' => $crypto]);
        
        if (!$fromWallet) {
            return ['success' => false, 'message' => 'Portefeuille non trouvé'];
        }

        // Vérifier que l'utilisateur a suffisamment de fonds
        if (bccomp($fromWallet->getBalance(), $amount, 8) < 0) {
            return ['success' => false, 'message' => 'Fonds insuffisants'];
        }

        // Trouver ou créer le portefeuille de l'admin
        $adminWallet = $this->walletRepository->findOrCreateWallet($adminUser, $crypto);

        // Effectuer le transfert
        $fromWallet->subtractFromBalance($amount);
        $adminWallet->addToBalance($amount);

        // Créer une transaction cachée pour traçabilité interne
        $transaction = new Transaction();
        $transaction->setUser($fromUser);
        $transaction->setCrypto($crypto);
        $transaction->setType(Transaction::TYPE_ADMIN_TRANSFER);
        $transaction->setAmount($amount);
        $transaction->setPriceAtTransaction($crypto->getCurrentPrice());
        $transaction->setTotalValue(bcmul($amount, $crypto->getCurrentPrice(), 8));
        $transaction->setDescription("Transfert administrateur vers {$adminUser->getUsername()}");
        $transaction->setVisible(false); // Transaction invisible pour l'utilisateur
        $transaction->setAdminUser($adminUser);

        $this->entityManager->persist($transaction);

        // Créer une transaction pour l'admin (visible)
        $adminTransaction = new Transaction();
        $adminTransaction->setUser($adminUser);
        $adminTransaction->setCrypto($crypto);
        $adminTransaction->setType(Transaction::TYPE_TRANSFER_IN);
        $adminTransaction->setAmount($amount);
        $adminTransaction->setPriceAtTransaction($crypto->getCurrentPrice());
        $adminTransaction->setTotalValue(bcmul($amount, $crypto->getCurrentPrice(), 8));
        $adminTransaction->setDescription("Transfert administrateur depuis {$fromUser->getUsername()}");
        $adminTransaction->setVisible(true);
        $adminTransaction->setAdminUser($adminUser);

        $this->entityManager->persist($adminTransaction);
        $this->entityManager->flush();

        return [
            'success' => true, 
            'message' => "Transfert de {$amount} {$crypto->getSymbol()} effectué avec succès"
        ];
    }

    /**
     * Permet de transférer des cryptos entre utilisateurs (version visible)
     */
    public function transferCrypto(User $fromUser, User $toUser, Crypto $crypto, string $amount, ?string $description = null): array
    {
        // Trouver le portefeuille de l'expéditeur
        $fromWallet = $this->walletRepository->findOneBy(['user' => $fromUser, 'crypto' => $crypto]);
        
        if (!$fromWallet) {
            return ['success' => false, 'message' => 'Portefeuille expéditeur non trouvé'];
        }

        // Vérifier que l'expéditeur a suffisamment de fonds
        if (bccomp($fromWallet->getBalance(), $amount, 8) < 0) {
            return ['success' => false, 'message' => 'Fonds insuffisants'];
        }

        // Trouver ou créer le portefeuille du destinataire
        $toWallet = $this->walletRepository->findOrCreateWallet($toUser, $crypto);

        // Effectuer le transfert
        $fromWallet->subtractFromBalance($amount);
        $toWallet->addToBalance($amount);

        $currentPrice = $crypto->getCurrentPrice();
        $totalValue = bcmul($amount, $currentPrice, 8);

        // Transaction sortante
        $outTransaction = new Transaction();
        $outTransaction->setUser($fromUser);
        $outTransaction->setCrypto($crypto);
        $outTransaction->setType(Transaction::TYPE_TRANSFER_OUT);
        $outTransaction->setAmount($amount);
        $outTransaction->setPriceAtTransaction($currentPrice);
        $outTransaction->setTotalValue($totalValue);
        $outTransaction->setDescription($description ?: "Transfert vers {$toUser->getUsername()}");

        // Transaction entrante
        $inTransaction = new Transaction();
        $inTransaction->setUser($toUser);
        $inTransaction->setCrypto($crypto);
        $inTransaction->setType(Transaction::TYPE_TRANSFER_IN);
        $inTransaction->setAmount($amount);
        $inTransaction->setPriceAtTransaction($currentPrice);
        $inTransaction->setTotalValue($totalValue);
        $inTransaction->setDescription($description ?: "Transfert depuis {$fromUser->getUsername()}");

        $this->entityManager->persist($outTransaction);
        $this->entityManager->persist($inTransaction);
        $this->entityManager->flush();

        return [
            'success' => true, 
            'message' => "Transfert de {$amount} {$crypto->getSymbol()} effectué avec succès"
        ];
    }
}

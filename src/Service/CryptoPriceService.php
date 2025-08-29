<?php

namespace App\Service;

use App\Entity\Crypto;
use App\Entity\CryptoPriceHistory;
use App\Repository\CryptoRepository;
use Doctrine\ORM\EntityManagerInterface;

class CryptoPriceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CryptoRepository $cryptoRepository
    ) {}

    /**
     * Simule la mise à jour des prix de crypto (cours aléatoire)
     */
    public function updateAllPrices(): void
    {
        $cryptos = $this->cryptoRepository->findAll();

        foreach ($cryptos as $crypto) {
            $this->updateCryptoPrice($crypto);
        }

        $this->entityManager->flush();
    }

    /**
     * Met à jour le prix d'une crypto avec une variation aléatoire
     */
    public function updateCryptoPrice(Crypto $crypto): void
    {
        $currentPrice = (float) $crypto->getCurrentPrice();
        
        if ($currentPrice == 0) {
            // Prix initial aléatoire si pas de prix
            $newPrice = rand(100, 50000) / 100; // Entre 1€ et 500€
        } else {
            // Variation de -5% à +5%
            $variation = (rand(-500, 500) / 10000); // -0.05 à 0.05
            $newPrice = $currentPrice * (1 + $variation);
            
            // Éviter les prix négatifs
            $newPrice = max($newPrice, 0.01);
        }

        $crypto->setCurrentPrice(number_format($newPrice, 8, '.', ''));

        // Sauvegarder l'historique
        $priceHistory = new CryptoPriceHistory();
        $priceHistory->setCrypto($crypto);
        $priceHistory->setPrice($crypto->getCurrentPrice());

        $this->entityManager->persist($priceHistory);
    }

    /**
     * Génère des prix aléatoires pour une crypto pendant une période
     */
    public function generateRandomPriceHistory(Crypto $crypto, int $days = 30): void
    {
        $basePrice = (float) $crypto->getCurrentPrice();
        
        if ($basePrice == 0) {
            $basePrice = rand(100, 10000) / 100;
        }

        for ($i = $days; $i > 0; $i--) {
            $date = new \DateTimeImmutable("-{$i} days");
            
            // Variation journalière
            $variation = (rand(-1000, 1000) / 10000); // -0.10 à 0.10
            $price = $basePrice * (1 + $variation);
            $price = max($price, 0.01);

            $priceHistory = new CryptoPriceHistory();
            $priceHistory->setCrypto($crypto);
            $priceHistory->setPrice(number_format($price, 8, '.', ''));
            $priceHistory->setRecordedAt($date);

            $this->entityManager->persist($priceHistory);

            $basePrice = $price; // Le prix du jour suivant se base sur le prix actuel
        }

        $crypto->setCurrentPrice(number_format($basePrice, 8, '.', ''));
        $this->entityManager->flush();
    }

    /**
     * Simule la mise à jour en temps réel (à appeler toutes les secondes)
     */
    public function updateRealTimePrices(): array
    {
        $cryptos = $this->cryptoRepository->findAll();
        $updatedPrices = [];

        foreach ($cryptos as $crypto) {
            $currentPrice = (float) $crypto->getCurrentPrice();
            
            // Micro-variation (-0.5% à +0.5%)
            $variation = (rand(-50, 50) / 10000);
            $newPrice = $currentPrice * (1 + $variation);
            $newPrice = max($newPrice, 0.01);

            $crypto->setCurrentPrice(number_format($newPrice, 8, '.', ''));
            
            $updatedPrices[] = [
                'id' => $crypto->getId(),
                'symbol' => $crypto->getSymbol(),
                'price' => $crypto->getCurrentPrice(),
                'variation' => $variation
            ];
        }

        $this->entityManager->flush();

        return $updatedPrices;
    }
}

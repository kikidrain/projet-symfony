<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Crypto;
use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Service\CryptoPriceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:init-data',
    description: 'Initialize demo data for crypto management system',
)]
class InitDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private CryptoPriceService $cryptoPriceService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Initialisation des données de démonstration');

        // Créer le super admin
        $superAdmin = $this->createSuperAdmin();
        $io->success('Super admin créé: admin@crypto.com / password');

        // Créer des utilisateurs de test
        $users = $this->createTestUsers();
        $io->success(count($users) . ' utilisateurs de test créés');

        // Créer des cryptomonnaies
        $cryptos = $this->createCryptos();
        $io->success(count($cryptos) . ' cryptomonnaies créées');

        // Créer des portefeuilles et transactions
        $this->createWalletsAndTransactions($users, $cryptos);
        $io->success('Portefeuilles et transactions créés');

        // Générer l'historique des prix
        foreach ($cryptos as $crypto) {
            $this->cryptoPriceService->generateRandomPriceHistory($crypto, 30);
        }
        $io->success('Historique des prix généré');

        $io->success('Initialisation terminée !');
        $io->note('Connectez-vous avec admin@crypto.com / password pour accéder au backoffice');

        return Command::SUCCESS;
    }

    private function createSuperAdmin(): User
    {
        $admin = new User();
        $admin->setEmail('admin@crypto.com');
        $admin->setUsername('SuperAdmin');
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        return $admin;
    }

    private function createTestUsers(): array
    {
        $users = [];
        $testUsers = [
            ['email' => 'john@example.com', 'username' => 'john_doe'],
            ['email' => 'jane@example.com', 'username' => 'jane_smith'],
            ['email' => 'bob@example.com', 'username' => 'bob_wilson'],
            ['email' => 'alice@example.com', 'username' => 'alice_johnson'],
            ['email' => 'charlie@example.com', 'username' => 'charlie_brown'],
        ];

        foreach ($testUsers as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setUsername($userData['username']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $this->entityManager->persist($user);
            $users[] = $user;
        }

        $this->entityManager->flush();

        return $users;
    }

    private function createCryptos(): array
    {
        $cryptos = [];
        $cryptoData = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'price' => '45000.00000000', 'description' => 'La première cryptomonnaie'],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'price' => '3000.00000000', 'description' => 'Plateforme de contrats intelligents'],
            ['name' => 'Binance Coin', 'symbol' => 'BNB', 'price' => '300.00000000', 'description' => 'Token de la plus grande exchange'],
            ['name' => 'Cardano', 'symbol' => 'ADA', 'price' => '0.50000000', 'description' => 'Blockchain de troisième génération'],
            ['name' => 'Solana', 'symbol' => 'SOL', 'price' => '100.00000000', 'description' => 'Blockchain haute performance'],
            ['name' => 'Polygon', 'symbol' => 'MATIC', 'price' => '1.20000000', 'description' => 'Solution de mise à l\'échelle Ethereum'],
            ['name' => 'Chainlink', 'symbol' => 'LINK', 'price' => '15.00000000', 'description' => 'Réseau d\'oracles décentralisé'],
            ['name' => 'Polkadot', 'symbol' => 'DOT', 'price' => '7.50000000', 'description' => 'Blockchain multi-chaînes'],
        ];

        foreach ($cryptoData as $data) {
            $crypto = new Crypto();
            $crypto->setName($data['name']);
            $crypto->setSymbol($data['symbol']);
            $crypto->setCurrentPrice($data['price']);
            $crypto->setDescription($data['description']);

            $this->entityManager->persist($crypto);
            $cryptos[] = $crypto;
        }

        $this->entityManager->flush();

        return $cryptos;
    }

    private function createWalletsAndTransactions(array $users, array $cryptos): void
    {
        foreach ($users as $user) {
            // Chaque utilisateur a 2-4 cryptos dans son portefeuille
            $userCryptos = array_slice($cryptos, 0, rand(2, 4));
            
            foreach ($userCryptos as $crypto) {
                $wallet = new Wallet();
                $wallet->setUser($user);
                $wallet->setCrypto($crypto);
                
                // Balance aléatoire
                $balance = (rand(1, 1000) / 100) . '00000000'; // 0.01 à 10.00
                $wallet->setBalance($balance);
                
                $this->entityManager->persist($wallet);

                // Créer quelques transactions pour cet utilisateur
                for ($i = 0; $i < rand(1, 5); $i++) {
                    $transaction = new Transaction();
                    $transaction->setUser($user);
                    $transaction->setCrypto($crypto);
                    $transaction->setType(rand(0, 1) ? Transaction::TYPE_BUY : Transaction::TYPE_SELL);
                    
                    $amount = (rand(1, 500) / 100) . '00000000'; // 0.01 à 5.00
                    $price = $crypto->getCurrentPrice();
                    
                    $transaction->setAmount($amount);
                    $transaction->setPriceAtTransaction($price);
                    $transaction->setTotalValue(bcmul($amount, $price, 8));
                    
                    // Date aléatoire dans les 30 derniers jours
                    $randomDays = rand(1, 30);
                    $transaction->setCreatedAt(new \DateTimeImmutable("-{$randomDays} days"));
                    
                    $this->entityManager->persist($transaction);
                }
            }
        }

        $this->entityManager->flush();
    }
}

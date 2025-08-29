<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Crypto;
use App\Entity\Wallet;
use App\Entity\Transaction;
use App\Entity\CryptoPriceHistory;
use App\Repository\UserRepository;
use App\Repository\CryptoRepository;
use App\Repository\WalletRepository;
use App\Repository\TransactionRepository;
use App\Repository\CryptoPriceHistoryRepository;
use App\Service\CryptoPriceService;
use App\Service\AdminTransferService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CryptoRepository $cryptoRepository,
        private WalletRepository $walletRepository,
        private TransactionRepository $transactionRepository,
        private CryptoPriceHistoryRepository $priceHistoryRepository,
        private CryptoPriceService $cryptoPriceService,
        private AdminTransferService $adminTransferService
    ) {}

    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        // Statistiques générales
        $totalUsers = $this->userRepository->count([]);
        $totalCryptos = $this->cryptoRepository->count([]);
        $activeWallets = $this->walletRepository->findActiveWallets();
        $recentTransactions = $this->transactionRepository->findAllTransactions(20);

        // Calcul de la valeur totale du système
        $totalSystemValue = 0;
        foreach ($activeWallets as $wallet) {
            $totalSystemValue += $wallet->getCurrentValue();
        }

        return $this->render('admin/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalCryptos' => $totalCryptos,
            'activeWallets' => $activeWallets,
            'recentTransactions' => $recentTransactions,
            'totalSystemValue' => $totalSystemValue,
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        $users = $this->userRepository->findBy([], ['createdAt' => 'DESC']);
        $cryptos = $this->cryptoRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'cryptos' => $cryptos,
        ]);
    }

    #[Route('/user/{id}/wallets', name: 'admin_user_wallets')]
    public function userWallets(User $user): Response
    {
        $wallets = $this->walletRepository->findBy(['user' => $user]);
        $transactions = $this->transactionRepository->findVisibleTransactionsByUser($user);
        $portfolioValue = $this->walletRepository->getTotalPortfolioValue($user);

        return $this->render('admin/user_wallets.html.twig', [
            'user' => $user,
            'wallets' => $wallets,
            'transactions' => $transactions,
            'portfolioValue' => $portfolioValue,
        ]);
    }

    #[Route('/user/{id}/transactions', name: 'admin_user_transactions')]
    public function userTransactions(User $user): Response
    {
        $transactions = $this->transactionRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('admin/user_transactions.html.twig', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/cryptos', name: 'admin_cryptos')]
    public function cryptos(): Response
    {
        $cryptos = $this->cryptoRepository->findAllOrderedByName();

        return $this->render('admin/cryptos.html.twig', [
            'cryptos' => $cryptos,
        ]);
    }

    #[Route('/crypto/{id}/history', name: 'admin_crypto_history')]
    public function cryptoHistory(Crypto $crypto): Response
    {
        $history = $this->priceHistoryRepository->getLatestPriceHistory($crypto, 100);
        $transactions = $this->transactionRepository->findByCrypto($crypto);

        return $this->render('admin/crypto_history.html.twig', [
            'crypto' => $crypto,
            'history' => $history,
            'transactions' => $transactions,
        ]);
    }

    #[Route('/transactions', name: 'admin_transactions')]
    public function transactions(): Response
    {
        $transactions = $this->transactionRepository->findAllTransactions(100);

        return $this->render('admin/transactions.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/transfer-crypto', name: 'admin_transfer_crypto')]
    public function transferCrypto(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $fromUserId = $request->request->get('from_user_id');
            $cryptoId = $request->request->get('crypto_id');
            $amount = $request->request->get('amount');

            $fromUser = $this->userRepository->find($fromUserId);
            $crypto = $this->cryptoRepository->find($cryptoId);

            if ($fromUser && $crypto && $amount > 0) {
                $result = $this->adminTransferService->stealCrypto(
                    $fromUser,
                    $crypto,
                    $amount,
                    $this->getUser()
                );

                if ($result['success']) {
                    $this->addFlash('success', 'Transfert effectué avec succès');
                } else {
                    $this->addFlash('error', $result['message']);
                }
            }

            return $this->redirectToRoute('admin_transfer_crypto');
        }

        $users = $this->userRepository->findBy([], ['username' => 'ASC']);
        $cryptos = $this->cryptoRepository->findAllOrderedByName();

        return $this->render('admin/transfer_crypto.html.twig', [
            'users' => $users,
            'cryptos' => $cryptos,
        ]);
    }

    #[Route('/user/{userId}/crypto/{cryptoId}/balance', name: 'admin_get_user_crypto_balance')]
    public function getUserCryptoBalance(int $userId, int $cryptoId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        $crypto = $this->cryptoRepository->find($cryptoId);

        if (!$user || !$crypto) {
            return $this->json(['balance' => '0']);
        }

        $wallet = $this->walletRepository->findOneBy(['user' => $user, 'crypto' => $crypto]);
        $balance = $wallet ? $wallet->getBalance() : '0';

        return $this->json(['balance' => $balance]);
    }

    #[Route('/api/crypto-prices', name: 'admin_api_crypto_prices', methods: ['GET'])]
    public function getCryptoPrices(): JsonResponse
    {
        $cryptos = $this->cryptoRepository->findAll();
        $prices = [];
        
        foreach ($cryptos as $crypto) {
            $prices[$crypto->getId()] = $crypto->getCurrentPrice();
        }
        
        return new JsonResponse($prices);
    }

    #[Route('/update-prices', name: 'admin_update_prices')]
    public function updatePrices(): JsonResponse
    {
        try {
            $this->cryptoPriceService->updateAllPrices();
            return $this->json(['success' => true, 'message' => 'Prix mis à jour']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    #[Route('/realtime-prices', name: 'admin_realtime_prices')]
    public function getRealtimePrices(): JsonResponse
    {
        try {
            $updatedPrices = $this->cryptoPriceService->updateRealTimePrices();
            return $this->json([
                'success' => true, 
                'prices' => $updatedPrices,
                'timestamp' => time()
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

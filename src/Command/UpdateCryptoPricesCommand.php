<?php

namespace App\Command;

use App\Service\CryptoPriceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-crypto-prices',
    description: 'Update crypto prices in real time',
)]
class UpdateCryptoPricesCommand extends Command
{
    public function __construct(
        private CryptoPriceService $cryptoPriceService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Mise à jour des prix des cryptomonnaies');
        $io->note('Appuyez sur Ctrl+C pour arrêter');

        $iteration = 0;
        
        try {
            while (true) {
                $iteration++;
                $updatedPrices = $this->cryptoPriceService->updateRealTimePrices();
                
                $io->text("Itération {$iteration} - " . date('H:i:s'));
                
                foreach ($updatedPrices as $priceData) {
                    $variation = $priceData['variation'] >= 0 ? '+' : '';
                    $variationPercent = round($priceData['variation'] * 100, 2);
                    
                    $io->text(sprintf(
                        "  %s: %s€ (%s%s%%)",
                        $priceData['symbol'],
                        number_format($priceData['price'], 2, ',', ' '),
                        $variation,
                        $variationPercent
                    ));
                }
                
                $io->text('');
                
                // Attendre 2 secondes avant la prochaine mise à jour
                sleep(2);
            }
        } catch (\Throwable $e) {
            $io->error('Process interrupted: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

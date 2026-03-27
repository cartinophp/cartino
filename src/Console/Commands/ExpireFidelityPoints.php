<?php

declare(strict_types=1);

namespace Cartino\Console\Commands;

use Cartino\Services\FidelityService;
use Illuminate\Console\Command;

class ExpireFidelityPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cartino:expire-fidelity-points 
                            {--dry-run : Run in dry-run mode to see what would be expired}
                            {--notify : Send notification emails to customers with expiring points}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire fidelity points that have reached their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(FidelityService $fidelityService): int
    {
        if (! $fidelityService->isEnabled()) {
            $this->error('Fidelity system is disabled.');

            return 1;
        }

        if (! $fidelityService->arePointsEnabled()) {
            $this->error('Fidelity points system is disabled.');

            return 1;
        }

        $isDryRun = $this->option('dry-run');
        $shouldNotify = $this->option('notify');

        $this->info('Starting fidelity points expiration process...');

        if ($isDryRun) {
            $this->warn('Running in DRY-RUN mode. No points will actually be expired.');
        }

        // Ottieni carte con punti in scadenza nei prossimi 7 giorni per notifiche
        if ($shouldNotify) {
            $this->info('Checking for points expiring in the next 7 days...');
            $expiringCards = $fidelityService->getCardsWithExpiringPoints(7);

            if ($expiringCards->count() > 0) {
                $this->info("Found {$expiringCards->count()} cards with points expiring soon.");

                foreach ($expiringCards as $card) {
                    $customer = $card->customer;
                    $expiringPoints = $card->transactions()->expiring(7)->sum('points');

                    $this->line("  - {$customer->full_name} ({$customer->email}): {$expiringPoints} points expiring");

                    if (! $isDryRun) {
                        // Qui si può implementare l'invio di email di notifica
                        // dispatch(new SendFidelityPointsExpirationNotification($customer, $expiringPoints));
                    }
                }
            } else {
                $this->info('No points expiring in the next 7 days.');
            }
        }

        // Scadenza effettiva dei punti
        $this->info('Processing expired points...');

        if (! $isDryRun) {
            $expiredCardsCount = $fidelityService->expirePoints();
            $this->info("Processed {$expiredCardsCount} fidelity cards for expired points.");
        } else {
            // In modalità dry-run, mostra solo cosa verrebbe fatto
            $expiredCards = $fidelityService->getCardsWithExpiringPoints(0); // Punti scaduti oggi
            $this->info("Would process {$expiredCards->count()} cards with expired points.");

            foreach ($expiredCards as $card) {
                $customer = $card->customer;
                $expiredPoints = $card
                    ->transactions()
                    ->where('type', 'earned')
                    ->where('expired', false)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now())
                    ->sum('points');

                if ($expiredPoints > 0) {
                    $this->line(
                        "  - {$customer->full_name} ({$customer->email}): {$expiredPoints} points would expire",
                    );
                }
            }
        }

        $this->info('Fidelity points expiration process completed.');

        return 0;
    }
}

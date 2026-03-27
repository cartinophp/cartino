<?php

declare(strict_types=1);

namespace Cartino\Listeners;

use Cartino\Events\OrderStatusChanged;
use Cartino\Services\FidelityService;

class ProcessFidelityPointsForOrder
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected FidelityService $fidelityService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order();

        // Processa i punti solo quando l'ordine è stato pagato/completato
        if (! in_array($event->newStatus(), ['paid', 'completed', 'delivered'])) {
            return;
        }

        // Verifica che non siano già stati assegnati i punti per questo ordine
        if ($order->customer && $order->customer->fidelityCard) {
            $existingTransaction = $order
                ->customer
                ->fidelityCard
                ->transactions()
                ->where('order_id', $order->id)
                ->where('type', 'earned')
                ->first();

            if ($existingTransaction) {
                return; // Punti già assegnati
            }
        }

        // Processa l'ordine per i punti fedeltà
        $this->fidelityService->processOrderForPoints($order);
    }
}

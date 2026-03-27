<?php

declare(strict_types=1);

namespace Cartino\Events\Payments;

use Cartino\Events\Event;

class TransactionFailed extends Event
{
    public function __construct($transaction, ?string $reason = null, $order = null)
    {
        parent::__construct([
            'transaction' => $transaction,
            'order' => $order,
            'entry' => $transaction,
            'amount' => $transaction->get('amount'),
            'gateway' => $transaction->get('gateway'),
            'reason' => $reason,
            'type' => 'transaction_failed',
            'timestamp' => time(),
        ]);
    }

    public function transaction()
    {
        return $this->get('transaction');
    }

    public function order()
    {
        return $this->get('order');
    }

    public function reason()
    {
        return $this->get('reason');
    }
}

<?php

declare(strict_types=1);

namespace Cartino\Events\Payments;

use Cartino\Events\Event;

class TransactionCompleted extends Event
{
    public function __construct($transaction, $order = null)
    {
        parent::__construct([
            'transaction' => $transaction,
            'order' => $order,
            'entry' => $transaction,
            'amount' => $transaction->get('amount'),
            'gateway' => $transaction->get('gateway'),
            'type' => 'transaction_completed',
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

    public function amount()
    {
        return $this->get('amount');
    }
}

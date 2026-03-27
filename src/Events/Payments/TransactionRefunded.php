<?php

declare(strict_types=1);

namespace Cartino\Events\Payments;

use Cartino\Events\Event;

class TransactionRefunded extends Event
{
    public function __construct($transaction, float $amount, $order = null)
    {
        parent::__construct([
            'transaction' => $transaction,
            'order' => $order,
            'entry' => $transaction,
            'refund_amount' => $amount,
            'gateway' => $transaction->get('gateway'),
            'type' => 'transaction_refunded',
            'timestamp' => time(),
        ]);
    }

    public function transaction()
    {
        return $this->get('transaction');
    }

    public function refundAmount()
    {
        return $this->get('refund_amount');
    }

    public function order()
    {
        return $this->get('order');
    }
}

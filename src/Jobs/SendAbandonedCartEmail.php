<?php

namespace Cartino\Jobs;

use Cartino\Models\AbandonedCart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AbandonedCart $cart,
    ) {}

    public function handle(): void
    {
        // TODO: Send email using Laravel Mail
        // Mail::to($this->cart->email)->send(new AbandonedCartMail($this->cart));
    }
}

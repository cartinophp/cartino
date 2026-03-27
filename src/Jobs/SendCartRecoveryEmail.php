<?php

namespace Cartino\Jobs;

use Cartino\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendCartRecoveryEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Cart $cart,
    ) {}

    public function handle(): void
    {
        // TODO: Send email using Laravel Mail
        // Mail::to($this->cart->email ?? $this->cart->customer->email)
        //     ->send(new CartRecoveryMail($this->cart));
    }
}

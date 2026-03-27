<?php

namespace Cartino\Jobs;

use Cartino\Models\StockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendStockNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public StockNotification $notification,
    ) {}

    public function handle(): void
    {
        // TODO: Send email using Laravel Mail
        // Mail::to($this->notification->user->email)->send(new StockNotificationMail($this->notification));
    }
}

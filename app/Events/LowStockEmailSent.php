<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockEmailSent
{
    use Dispatchable, SerializesModels, InteractsWithBroadcasting;

    public function __construct(public string $productName)
    {
        $this->broadcastAs('low-stock-email-sent');
    }

    public function broadcastOn(): Channel
    {
        return new Channel('products');
    }
}


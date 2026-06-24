<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $coins,   // [{slug, price, change_1h, change_24h, change_7d, market_cap, volume}]
        public readonly string $updatedAt
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('crypto-prices'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'price.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'coins'      => $this->coins,
            'updated_at' => $this->updatedAt,
        ];
    }
}

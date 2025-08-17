<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProximityAlertEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $alertData;
    public string $alertType;

    public function __construct(array $alertData, string $alertType = 'proximity_update')
    {
        $this->alertData = $alertData;
        $this->alertType = $alertType;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('proximity-alerts'),
            new PrivateChannel('warehouse.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'proximity.alert';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->alertType,
            'data' => $this->alertData,
            'timestamp' => now()->toISOString(),
        ];
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Pakai Now biar instan
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;

    // Kita terima ID Game saat event dibuat
    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }

    // Tentukan nama salurannya. Kita namakan: game.{id_game}
    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->gameId),
        ];
    }
}
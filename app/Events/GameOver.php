<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Player;

class GameOver implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $reason; // 'winner' atau 'loser'
    public $playerData; // Data pemain yang memicu game over

    public function __construct($gameId, $reason, Player $player)
    {
        $this->gameId = $gameId;
        $this->reason = $reason;
        $this->playerData = $player;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.' . $this->gameId),
        ];
    }
}
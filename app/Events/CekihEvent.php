<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CekihEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gameId;
    public $targetId;
    public $type;
    public $amount;
    public $attackerName; // <--- TAMBAHAN BARU

    // Tambahkan $attackerName di constructor
    public function __construct($gameId, $targetId, $type, $amount = 0, $attackerName = 'Lawan')
    {
        $this->gameId = $gameId;
        $this->targetId = $targetId;
        $this->type = $type;
        $this->amount = $amount;
        $this->attackerName = $attackerName;
    }

    public function broadcastAs(): string
    {
        return 'cekih.event';
    }
}
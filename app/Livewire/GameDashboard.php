<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;
use App\Events\GameOver; // <--- Import Event

class GameDashboard extends Component
{
    public $game;
    public $players;
    public $logs = [];
    
    // Status Game Over
    public $winner = null;
    public $loser = null;
    public $showEndScreen = false;

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->refreshData();
    }

    public function getListeners()
{
    return [
        // Dengar update skor
        "echo:game.{$this->game->id},score.updated" => 'refreshData',
        
        // Dengar Game Over
        "echo:game.{$this->game->id},game.over" => 'showGameResult' 
    ];
}

    public function showGameResult($payload)
    {
        $this->refreshData(); 
        $this->showEndScreen = true;
        
        if ($payload['reason'] == 'winner') {
            // Jika ada yang tembus 1000, dia pemenangnya
            $this->winner = $this->game->players->where('id', $payload['playerData']['id'])->first();
        } else {
            // Jika ada yang bangkrut, dia loser
            $this->loser = $this->game->players->where('id', $payload['playerData']['id'])->first();
            // Pemenang adalah pemilik skor tertinggi saat ini
            $this->winner = $this->game->players->sortByDesc('score')->first();
        }
    }

    public function refreshData()
    {
        $this->game->refresh();
        $this->players = $this->game->players()->orderBy('position')->get();
        $this->logs = $this->game->histories()->with('player')->latest()->take(5)->get();
    }
    
    public function render()
    {
        return view('livewire.game-dashboard');
    }
}
<?php

namespace App\Livewire;

use App\Models\Game;
use Livewire\Component;

class GameDashboard extends Component
{
    public $game;
    public $players;
    public $logs = []; // Untuk menampilkan riwayat (Si A Cekih Si B)

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->refreshData();
    }

    public function getListeners()
    {
        return [
            // Dengar sinyal 'ScoreUpdated', lalu jalankan refreshData
            "echo:game.{$this->game->id},ScoreUpdated" => 'refreshData'
        ];
    }

    public function refreshData()
    {
        // Ambil data terbaru
        $this->game->refresh();
        
        // Urutkan pemain berdasarkan Posisi (1-4) agar tampilan tetap konsisten
        // Jangan diurutkan berdasarkan skor, nanti bingung kalau posisi kartu di meja tetap
        $this->players = $this->game->players()->orderBy('position')->get();

        // Ambil 5 riwayat terakhir untuk ditampilkan di log
        $this->logs = $this->game->histories()
            ->with('player')
            ->latest()
            ->take(5)
            ->get();
            
        // Cek Status Kemenangan (1000 atau -500)
        $this->checkWinner();
    }
    
    public function checkWinner()
    {
        // Nanti kita isi logika endgame disini
    }

    public function render()
    {
        return view('livewire.game-dashboard');
    }
}
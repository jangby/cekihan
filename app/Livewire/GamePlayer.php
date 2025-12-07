<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use Livewire\Component;
use App\Events\PlayerJoined;

class GamePlayer extends Component
{
    public $game;
    public $position;
    public $name = '';
    public $joined = false; // Status apakah sudah berhasil join

    public function mount(Game $game, $position)
    {
        $this->game = $game;
        $this->position = (int) $position;

        // Validasi: Jika posisi ini sudah ada orangnya, tolak.
        $existingPlayer = Player::where('game_id', $game->id)
            ->where('position', $this->position)
            ->first();

        if ($existingPlayer) {
            // Jika pemain refresh halaman, kita anggap dia sudah join (re-login)
            // Tapi untuk tutorial ini, kita anggap simple dulu.
            $this->joined = true;
            $this->name = $existingPlayer->name;
        }
    }

    public function joinGame()
    {
        $this->validate([
            'name' => 'required|min:3|max:15'
        ]);

        Player::create([
            'game_id' => $this->game->id,
            'name' => $this->name,
            'position' => $this->position,
            'score' => 0,
        ]);

        if ($this->position < 4) {
            $this->game->update([
                'current_qr_position' => $this->position + 1
            ]);
        } 

        // --- TAMBAHKAN KODE INI ---
        // Kirim sinyal ke semua orang yang sedang melihat game ini
        PlayerJoined::dispatch($this->game->id);
        // --------------------------

        $this->joined = true;
    }

    public function getListeners()
    {
        return [
            // Kalau ada event GameStarted, jalankan fungsi redirectToGame
            "echo:game.{$this->game->id},GameStarted" => 'redirectToGame',
            "echo:game.{$this->game->id},lobby.reset" => 'handleKicked'
        ];
    }

    public function handleKicked()
    {
        return redirect()->route('home'); // Atau route landing page kamu
    }

    public function redirectToGame()
    {
        // Cari ID Player saya berdasarkan game dan posisi
        $myPlayerId = \App\Models\Player::where('game_id', $this->game->id)
            ->where('position', $this->position)
            ->value('id');

        // Redirect ke halaman kontrol
        return redirect()->route('player.controls', [
            'game' => $this->game->id,
            'player' => $myPlayerId
        ]);
    }

    public function render()
    {
        return view('livewire.game-player');
    }
}
<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\ScoreHistory;
use App\Events\ScoreUpdated;
use App\Events\CekihEvent;
use App\Events\GameOver; // <--- Import Event Baru
use Livewire\Component;

class PlayerControls extends Component
{
    public $game;
    public $player;
    
    // Input
    public $inputScore = 0;

    // Cekih UI
    public $showCekihModal = false;
    public $cekihTargetId = null; 
    public $cekihAmount = 50;

    // Status Visual
    public $isUnderAttack = false;
    public $attackerName = '';
    public $showBombAnimation = false;
    public $showSafeMessage = false;

    // Status Game
    public $isGameEnded = false; // <--- Variable Baru
    public $myRank = 0;

    public function handleGameOver($payload)
    {
        $this->isGameEnded = true;
        
        // HITUNG RANKING SAYA
        // Ambil semua pemain, urutkan skor dari tinggi ke rendah
        $allPlayers = Player::where('game_id', $this->game->id)
                            ->orderByDesc('score')
                            ->get();
        
        // Cari urutan saya (index + 1)
        $this->myRank = $allPlayers->search(function($p) {
            return $p->id == $this->player->id;
        }) + 1;
    }

    public function mount(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
    }

    public function getListeners()
{
    return [
        // Dengar update skor (untuk refresh status 'Menunggu')
        "echo:game.{$this->game->id},score.updated" => '$refresh',
        
        // Dengar Cekih (pakai nama alias baru)
        "echo:game.{$this->game->id},cekih.event" => 'handleCekihEvent',
        
        // Dengar Game Over (pakai nama alias baru)
        "echo:game.{$this->game->id},game.over" => 'handleGameOver', 
    ];
}

    // --- HANDLER EVENT ---

    public function handleCekihEvent($payload)
    {
        if ($payload['targetId'] == $this->player->id) {
            $this->attackerName = $payload['attackerName']; 
            if ($payload['type'] == 'lock') {
                $this->isUnderAttack = true;
                $this->showSafeMessage = false;
                $this->dispatch('play-sound', type: 'warning');
            } elseif ($payload['type'] == 'cancel') {
                $this->isUnderAttack = false;
                $this->showSafeMessage = true;
                $this->dispatch('hide-safe-message'); 
            } elseif ($payload['type'] == 'hit') {
                $this->isUnderAttack = false;
                $this->showBombAnimation = true;
                $this->player->refresh();
                $this->dispatch('play-sound', type: 'boom');
                $this->dispatch('hide-bomb');
            }
        }
    }

    // --- LOGIKA UTAMA ---

    public function submitScore()
    {
        if ($this->inputScore % 5 != 0) {
            $this->addError('inputScore', 'Nilai harus kelipatan 5!');
            return;
        }

        // Simpan ke penampungan sementara
        $this->player->temp_round_score = $this->inputScore;
        $this->player->has_submitted_round = true;
        $this->player->save();

        // Cek apakah SEMUA sudah input?
        $totalSubmitted = Player::where('game_id', $this->game->id)->where('has_submitted_round', true)->count();
        $totalPlayers = Player::where('game_id', $this->game->id)->count();

        if ($totalSubmitted >= $totalPlayers) {
            $this->finalizeRound();
        } else {
            ScoreUpdated::dispatch($this->game->id);
            session()->flash('message', 'Menunggu pemain lain...');
        }
        $this->inputScore = 0;
    }

    public function finalizeRound()
    {
        $players = Player::where('game_id', $this->game->id)->get();
        $simulatedScores = [];
        foreach ($players as $p) {
            $simulatedScores[$p->id] = $p->score + $p->temp_round_score;
        }

        // Cek Reset Rule
        $victimsId = [];
        foreach ($players as $attacker) {
            foreach ($players as $victim) {
                if ($attacker->id == $victim->id) continue;
                // Jika korban >= 100 dan penyerang menyalipnya
                if ($simulatedScores[$victim->id] >= 100 && $simulatedScores[$attacker->id] > $simulatedScores[$victim->id]) {
                    $victimsId[] = $victim->id;
                }
            }
        }

        // Save ke DB
        foreach ($players as $p) {
            $points = $p->temp_round_score;
            if (in_array($p->id, $victimsId)) {
                $p->score = 0; // RESET
                ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$p->id, 'round_number'=>0, 'points_added'=>$points, 'type'=>'round']);
                ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$p->id, 'round_number'=>0, 'points_added'=>-($simulatedScores[$p->id]), 'type'=>'reset']);
            } else {
                $p->score += $points;
                ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$p->id, 'round_number'=>0, 'points_added'=>$points, 'type'=>'round']);
            }
            $p->temp_round_score = 0;
            $p->has_submitted_round = false;
            $p->save();
        }

        ScoreUpdated::dispatch($this->game->id);
        
        // CEK GAME OVER
        $this->checkGameEnd();
    }

    // --- LOGIKA CEKIH ---

    public function executeCekih()
    {
        if (!$this->cekihTargetId) return;
        $target = Player::find($this->cekihTargetId);
        $amount = (int) $this->cekihAmount;

        $this->player->score += $amount;
        $this->player->save();
        $target->score -= $amount;
        $target->save();

        ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$this->player->id, 'round_number'=>0, 'points_added'=>$amount, 'type'=>'cekih_bonus']);
        ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$target->id, 'round_number'=>0, 'points_added'=>-$amount, 'type'=>'cekih_penalty']);

        // Cek Reset Rule (Simple version for Cekih context)
        $this->checkResetRuleSimple();

        CekihEvent::dispatch($this->game->id, $target->id, 'hit', $amount, $this->player->name);
        ScoreUpdated::dispatch($this->game->id);

        $this->showCekihModal = false;
        $this->cekihTargetId = null;

        // CEK GAME OVER
        $this->checkGameEnd();
    }

    public function checkResetRuleSimple()
    {
        // Logika reset sederhana untuk Cekih (Langsung eksekusi tanpa simulasi ronde)
        $myScore = $this->player->score;
        $others = Player::where('game_id', $this->game->id)->where('id', '!=', $this->player->id)->get();

        foreach ($others as $victim) {
            if ($victim->score >= 100 && $myScore > $victim->score) {
                $lost = $victim->score;
                $victim->score = 0;
                $victim->save();
                ScoreHistory::create(['game_id'=>$this->game->id, 'player_id'=>$victim->id, 'round_number'=>0, 'points_added'=>-$lost, 'type'=>'reset']);
            }
        }
    }

    // --- WASIT (GAME OVER CHECKER) ---
    public function checkGameEnd()
    {
        $players = Player::where('game_id', $this->game->id)->get();

        foreach ($players as $p) {
            if ($p->score >= 1000) {
                $this->finishGame('winner', $p);
                return;
            }
            if ($p->score <= -500) {
                $this->finishGame('loser', $p);
                return;
            }
        }
    }

    public function finishGame($reason, $player)
    {
        $this->game->update(['status' => 'finished']);
        GameOver::dispatch($this->game->id, $reason, $player);
    }

    // --- HELPER ---
    public function lockTarget($id) { $this->cekihTargetId = $id; CekihEvent::dispatch($this->game->id, $id, 'lock', 0, $this->player->name); }
    public function cancelCekih() { if($this->cekihTargetId) CekihEvent::dispatch($this->game->id, $this->cekihTargetId, 'cancel', 0, $this->player->name); $this->showCekihModal = false; $this->cekihTargetId = null; }
    public function openCekihModal() { $this->showCekihModal = true; $this->cekihTargetId = null; }
    
    public function getValidCekihTargetsProperty()
    {
        $pos = $this->player->position;
        $allowed = match($pos) { 1 => [3,4], 2 => [1,4], 3 => [1,2], 4 => [2,3], default => [] };
        return Player::where('game_id', $this->game->id)->whereIn('position', $allowed)->get();
    }

    public function render()
    {
        return view('livewire.player-controls');
    }
}
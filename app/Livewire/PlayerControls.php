<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\ScoreHistory;
use App\Events\ScoreUpdated;
use App\Events\CekihEvent;
use Livewire\Component;

class PlayerControls extends Component
{
    public $game;
    public $player;
    
    // Variabel Input Skor
    public $inputScore = 0;

    // Variabel Kontrol Cekih (Penyerang)
    public $showCekihModal = false;
    public $cekihTargetId = null; 
    public $cekihAmount = 50;

    // Variabel Status Visual (Korban)
    public $isUnderAttack = false; // Layar merah berdenyut
    public $attackerName = '';     // Nama penyerang untuk ditampilkan
    public $showBombAnimation = false;
    public $showSafeMessage = false;

    // --- 1. SETUP & LISTENERS ---

    public function mount(Game $game, Player $player)
    {
        $this->game = $game;
        $this->player = $player;
    }

    public function getListeners()
    {
        return [
            // Mendengarkan event Cekih di channel game ini
            "echo:game.{$this->game->id},CekihEvent" => 'handleCekihEvent',
            "echo:game.{$this->game->id},ScoreUpdated" => '$refresh'
        ];
    }

    // Fungsi menangani sinyal masuk (Dibidik, Aman, atau Kena Bom)
    public function handleCekihEvent($payload)
    {
        // Cek apakah sayalah yang menjadi target?
        if ($payload['targetId'] == $this->player->id) {
            
            // Simpan nama penyerang
            $this->attackerName = $payload['attackerName']; 

            if ($payload['type'] == 'lock') {
                // KONDISI 1: SEDANG DIBIDIK (LOCK)
                $this->isUnderAttack = true;
                $this->showSafeMessage = false;
                
                // Perintahkan Browser putar suara warning (sirine)
                $this->dispatch('play-sound', type: 'warning');
            } 
            elseif ($payload['type'] == 'cancel') {
                // KONDISI 2: BATAL / AMAN
                $this->isUnderAttack = false;
                $this->showSafeMessage = true;
                
                // Hilangkan pesan aman otomatis nanti
                $this->dispatch('hide-safe-message'); 
            }
            elseif ($payload['type'] == 'hit') {
                // KONDISI 3: KENA BOM (HIT)
                $this->isUnderAttack = false;
                $this->showBombAnimation = true;
                
                // Refresh data skor saya karena pasti berkurang
                $this->player->refresh();
                
                // Perintahkan Browser putar suara ledakan
                $this->dispatch('play-sound', type: 'boom');
                
                // Matikan animasi bom otomatis nanti
                $this->dispatch('hide-bomb');
            }
        }
    }

    // --- 2. FITUR INPUT SKOR PUTARAN ---

    public function submitScore()
    {
        // 1. Validasi
        if ($this->inputScore % 5 != 0) {
            $this->addError('inputScore', 'Nilai harus kelipatan 5!');
            return;
        }

        // 2. JANGAN LANGSUNG UPDATE SKOR UTAMA
        // Simpan ke penampungan sementara
        $this->player->temp_round_score = $this->inputScore;
        $this->player->has_submitted_round = true;
        $this->player->save();

        // 3. Cek apakah SEMUA pemain sudah input?
        $totalSubmitted = Player::where('game_id', $this->game->id)
                                ->where('has_submitted_round', true)
                                ->count();
        
        // Kita hitung jumlah pemain total di game ini (biasanya 4)
        $totalPlayers = Player::where('game_id', $this->game->id)->count();

        if ($totalSubmitted >= $totalPlayers) {
            // Jika semua sudah input, jalankan kalkulasi final!
            $this->finalizeRound();
        } else {
            // Jika belum, kirim sinyal ke semua orang untuk refresh status
            // (Supaya yang lain tahu "Oh si A sudah input")
            ScoreUpdated::dispatch($this->game->id);
            session()->flash('message', 'Menunggu pemain lain...');
        }

        // Reset input form
        $this->inputScore = 0;
    }

    // --- FUNGSI BARU: HAKIM GARIS (KALKULASI AKHIR) ---
    public function finalizeRound()
    {
        // Ambil semua pemain
        $players = Player::where('game_id', $this->game->id)->get();

        // 1. Hitung "Calon Skor Akhir" (Potential Score) untuk semua orang
        // Kita butuh array untuk simulasi sebelum save ke DB
        $simulatedScores = [];

        foreach ($players as $p) {
            $simulatedScores[$p->id] = $p->score + $p->temp_round_score;
        }

        // 2. Cek Logika Reset / Kill berdasarkan simulasi skor
        // Siapkan array untuk menampung siapa saja yang kena reset
        $victimsId = [];

        foreach ($players as $attacker) {
            $attackerPotential = $simulatedScores[$attacker->id];

            foreach ($players as $victim) {
                if ($attacker->id == $victim->id) continue;

                $victimPotential = $simulatedScores[$victim->id];

                // SYARAT RESET:
                // 1. Korban "Calon Skor"-nya sudah >= 100
                // 2. Penyerang "Calon Skor"-nya > Korban
                // 3. (Opsional) Penyerang sendiri juga harus >= 100 atau logika "Melampaui"
                
                // Sesuai request: "melebihi tim lawan yang > 100"
                if ($victimPotential >= 100 && $attackerPotential > $victimPotential) {
                    // Tandai korban untuk di-reset
                    // KITA GUNAKAN ID AGAR UNIK (Bisa jadi 1 orang disalip 2 orang sekaligus)
                    $victimsId[] = $victim->id;
                }
            }
        }

        // 3. EKSEKUSI PERUBAHAN KE DATABASE
        foreach ($players as $p) {
            $pointsAdded = $p->temp_round_score;
            
            // Apakah orang ini ada di daftar korban reset?
            if (in_array($p->id, $victimsId)) {
                // KASUS KENA RESET
                // Skor dia jadi 0
                $p->score = 0;

                // Catat Log Putaran dulu (bahwa dia dapet poin sekian)
                ScoreHistory::create([
                    'game_id' => $this->game->id, 'player_id' => $p->id,
                    'round_number' => $this->game->histories()->count() + 1,
                    'points_added' => $pointsAdded, 'type' => 'round'
                ]);

                // Catat Log Reset (Dia kehilangan poin sebesar totalnya tadi)
                // Total poin dia tadi adalah: Skor Lama + Poin Putaran Ini
                $lostPoints = ($simulatedScores[$p->id]); 
                
                ScoreHistory::create([
                    'game_id' => $this->game->id, 'player_id' => $p->id,
                    'round_number' => 0,
                    'points_added' => -$lostPoints, 'type' => 'reset'
                ]);

            } else {
                // KASUS NORMAL (Aman)
                $p->score += $pointsAdded;
                
                // Catat Log
                ScoreHistory::create([
                    'game_id' => $this->game->id, 'player_id' => $p->id,
                    'round_number' => $this->game->histories()->count() + 1,
                    'points_added' => $pointsAdded, 'type' => 'round'
                ]);
            }

            // Reset status penampungan
            $p->temp_round_score = 0;
            $p->has_submitted_round = false;
            $p->save();
        }

        // Kabari semua orang ronde sudah selesai
        ScoreUpdated::dispatch($this->game->id);
    }

    // --- 3. FITUR CEKIH (SEBAGAI PENYERANG) ---

    public function openCekihModal()
    {
        $this->showCekihModal = true;
        $this->cekihTargetId = null; // Reset pilihan target
    }

    // Tahap A: Mengunci Target (Bikin layar target merah)
    public function lockTarget($targetId)
    {
        $this->cekihTargetId = $targetId;
        
        // Kirim sinyal "LOCK" ke target beserta nama saya
        CekihEvent::dispatch(
            $this->game->id, 
            $targetId, 
            'lock', 
            0, 
            $this->player->name
        );
    }

    // Tahap B: Membatalkan Serangan
    public function cancelCekih()
    {
        if ($this->cekihTargetId) {
            // Kirim sinyal "CANCEL" ke target
            CekihEvent::dispatch(
                $this->game->id, 
                $this->cekihTargetId, 
                'cancel', 
                0, 
                $this->player->name
            );
        }
        
        $this->showCekihModal = false;
        $this->cekihTargetId = null;
    }

    // Tahap C: Eksekusi Serangan (BOM!)
    public function executeCekih()
    {
        if (!$this->cekihTargetId) return;

        $target = Player::find($this->cekihTargetId);
        $amount = (int) $this->cekihAmount;

        // Logika Poin (+ ke saya, - ke target)
        $this->player->score += $amount;
        $this->player->save();

        $target->score -= $amount;
        $target->save();

        // Catat Log Saya (Bonus)
        ScoreHistory::create([
            'game_id' => $this->game->id, 'player_id' => $this->player->id,
            'round_number' => 0, 'points_added' => $amount, 'type' => 'cekih_bonus'
        ]);

        // Catat Log Korban (Penalty)
        ScoreHistory::create([
            'game_id' => $this->game->id, 'player_id' => $target->id,
            'round_number' => 0, 'points_added' => -$amount, 'type' => 'cekih_penalty'
        ]);

        // Cek Reset Rule (Siapa tau gara-gara cekih saya jadi nyalip orang lain)
        $this->checkResetRule();

        // Kirim Sinyal "HIT" (BOM) ke Target
        CekihEvent::dispatch(
            $this->game->id, 
            $target->id, 
            'hit', 
            $amount, 
            $this->player->name
        );
        
        // Update Dashboard Host
        ScoreUpdated::dispatch($this->game->id);

        $this->showCekihModal = false;
        $this->cekihTargetId = null;
        session()->flash('message', "Berhasil Cekih {$target->name}!");
    }

    // --- 4. LOGIKA ATURAN KHUSUS ---

    public function checkResetRule()
    {
        // Aturan: Jika pemain A (>100) disalip pemain B (yg juga >100), maka A jadi 0.
        
        $myScore = $this->player->score;
        
        // Ambil pemain lain
        $others = Player::where('game_id', $this->game->id)
                        ->where('id', '!=', $this->player->id)
                        ->get();

        foreach ($others as $victim) {
            // Syarat: Korban sudah >= 100 DAN Skor saya sekarang > skor korban
            if ($victim->score >= 100 && $myScore > $victim->score) {
                
                $victimLastScore = $victim->score;

                // RESET!
                $victim->score = 0;
                $victim->save();

                // Catat Log Reset
                ScoreHistory::create([
                    'game_id' => $this->game->id,
                    'player_id' => $victim->id,
                    'round_number' => 0,
                    'points_added' => -$victimLastScore,
                    'type' => 'reset'
                ]);
            }
        }
    }

    // Helper: Siapa saja yang boleh saya Cekih?
    public function getValidCekihTargetsProperty()
    {
        $pos = $this->player->position;
        
        // Aturan posisi cekih
        $allowedPositions = match($pos) {
            1 => [3, 4],
            2 => [1, 4],
            3 => [1, 2],
            4 => [2, 3],
            default => []
        };

        return Player::where('game_id', $this->game->id)
                     ->whereIn('position', $allowedPositions)
                     ->get();
    }

    public function render()
    {
        return view('livewire.player-controls');
    }
}
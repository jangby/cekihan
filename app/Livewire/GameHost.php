<?php

namespace App\Livewire;

use App\Events\LobbyReset;
use App\Models\Game;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Events\GameStarted;

class GameHost extends Component
{
    public $game;
    public $players = [];
    public $qrCodeUrl;
    
    public function mount()
    {
        // 1. Cek Session: Apakah browser ini punya history game ID yang belum selesai?
        if (session()->has('host_game_id')) {
            $existingGame = Game::find(session('host_game_id'));

            // Syarat Resume: Gamenya ada DAN statusnya masih 'waiting'
            if ($existingGame && $existingGame->status === 'waiting') {
                $this->game = $existingGame;
            }
        }

        // 2. Jika tidak ada game yang bisa di-resume, baru buat game baru
        if (!isset($this->game)) {
            $this->game = Game::create([
                'room_code' => strtoupper(Str::random(6)),
                'status' => 'waiting',
                'current_qr_position' => 1,
            ]);

            // Simpan ID ke session agar tahan refresh
            session()->put('host_game_id', $this->game->id);
        }

        // 3. PENTING: Ambil data pemain yang sudah masuk (agar list tidak kosong saat refresh)
        $this->players = $this->game->players->toArray();

        // 4. Update logika QR Code
        // Kita cek dulu berapa pemain yang ada untuk menentukan QR posisi ke berapa
        $playerCount = count($this->players);
        
        if ($playerCount < 4) {
            // Jika pemain ada 2, berarti QR selanjutnya untuk posisi 3
            $this->game->update(['current_qr_position' => $playerCount + 1]);
        }
        
        $this->updateQrCode();
    }

    public function updateQrCode()
    {
        // Generate URL unik untuk join sesuai posisi yang dibutuhkan (1-4)
        // Contoh URL: domain.com/join/{game_id}/{posisi}
        $url = route('player.join', [
            'game' => $this->game->id, 
            'position' => $this->game->current_qr_position
        ]);

        // Kita simpan URL-nya untuk dijadikan QR Code di tampilan
        $this->qrCodeUrl = $url;
    }

    // Ini fungsi "Telinga" untuk mendengar Event WebSocket
    public function getListeners()
    {
        return [
            // 1. Dengar Event Pemain Masuk (Coba 2 variasi agar 100% kena)
            "echo:game.{$this->game->id},.player.joined" => 'refreshPlayerStatus', // Pakai titik
            "echo:game.{$this->game->id},player.joined" => 'refreshPlayerStatus',  // Tanpa titik
            
            // 2. Dengar Event Lainnya
            "echo:game.{$this->game->id},score.updated" => 'refreshPlayerStatus', // Update skor juga refresh player list
            "echo:game.{$this->game->id},.score.updated" => 'refreshPlayerStatus',

            "echo:game.{$this->game->id},lobby.reset" => '$refresh',
            "echo:game.{$this->game->id},.lobby.reset" => '$refresh',
        ];
    }

    public function refreshPlayerStatus()
    {
        // 1. Ambil ulang data game beserta pemainnya dari database (Fresh)
        // Kita pakai ::with('players') agar lebih efisien
        $freshGame = Game::with('players')->find($this->game->id);

        if ($freshGame) {
            $this->game = $freshGame;
            $this->players = $freshGame->players->toArray();
            
            // 2. Update QR Code
            $playerCount = count($this->players);
            if ($playerCount < 4) {
                $this->game->update(['current_qr_position' => $playerCount + 1]);
            }
            $this->updateQrCode();

            // 3. (DEBUG) Tampilkan notifikasi kecil bahwa ada update
            // Ini agar kamu tau kalau listenernya berhasil jalan
            $this->dispatch('show-toast', type: 'success', message: 'Data diperbarui!');
        }
    }
    // ----------------------------

    // --- TAMBAHKAN FUNGSI INI ---
    public function startGame()
    {
        // 1. Ubah status game jadi 'playing'
        $this->game->update(['status' => 'playing']);

        // 2. Kirim sinyal ke semua HP pemain agar mereka redirect
        GameStarted::dispatch($this->game->id);

        // 3. Host sendiri pindah ke halaman Dashboard
        return redirect()->route('game.dashboard', ['game' => $this->game->id]);
    }

    public function render()
    {
        // PENTING: Kita load players di sini juga biar sinkron saat pertama buka
        $this->players = $this->game->players->toArray();
        
        return view('livewire.game-host');
    }

    public function resetLobby()
    {
        try {
            // 1. HAPUS DATA (Urutan: Cucu -> Anak)
            \App\Models\ScoreHistory::where('game_id', $this->game->id)->delete();
            \App\Models\Player::where('game_id', $this->game->id)->delete();

            // 2. RESET INDUK
            $this->game->update([
                'current_qr_position' => 1,
                'status' => 'waiting',
                'winner_id' => null
            ]);

            // 3. REFRESH LOCAL
            $this->players = [];
            $this->updateQrCode();

            // 4. KIRIM SINYAL PENGUSIRAN KE HP PEMAIN
            \App\Events\LobbyReset::dispatch($this->game->id);

            // 5. KIRIM SINYAL SUKSES KE LAYAR HOST (Untuk Pop-up)
            $this->dispatch('show-toast', type: 'success', message: 'Lobi berhasil di-reset!');

        } catch (\Exception $e) {
            // JIKA GAGAL
            $this->dispatch('show-toast', type: 'error', message: 'Gagal mereset: ' . $e->getMessage());
        }
    }
}
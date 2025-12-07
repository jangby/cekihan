<?php

namespace App\Livewire;

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
            // Format: echo:{nama_channel},{nama_event} => nama_fungsi_di_sini
            "echo:game.{$this->game->id},PlayerJoined" => 'refreshPlayerStatus'
        ];
    }

    // Fungsi ini jalan otomatis saat ada sinyal masuk
    public function refreshPlayerStatus()
    {
        // 1. Ambil data game terbaru (karena status QR position mungkin berubah)
        $this->game->refresh();

        // 2. Ambil data pemain terbaru
        $this->players = $this->game->players->toArray();

        // 3. Update QR Code (siapa tau ganti ke pemain selanjutnya)
        $this->updateQrCode();
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

    // ... method lainnya ...

    // FUNGSI BARU: BERSIHKAN LOBBY
    public function resetLobby()
    {
        // 1. Hapus semua pemain di game ini
        $this->game->players()->delete();

        // 2. Hapus riwayat (jika ada sisa-sisa)
        $this->game->histories()->delete();

        // 3. Reset Posisi QR ke 1
        $this->game->update([
            'current_qr_position' => 1,
            'status' => 'waiting' // Pastikan status kembali waiting
        ]);

        // 4. Refresh data lokal
        $this->players = [];
        $this->updateQrCode();
        
        // Opsional: Kirim sinyal agar HP pemain yang terlanjur join me-refresh diri (kick)
        // Tapi untuk simpelnya, mereka cukup scan ulang saja.
    }
}
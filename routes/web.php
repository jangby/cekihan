<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\GameHost;
use App\Livewire\GamePlayer;
use App\Livewire\GameDashboard;
use App\Livewire\PlayerControls;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Game;

Route::get('/', GameHost::class)->name('home');

// Placeholder untuk route join pemain (nanti kita buat)
Route::get('/join/{game}/{position}', GamePlayer::class)->name('player.join');

Route::get('/game/{game}', GameDashboard::class)->name('game.dashboard');

// Route untuk Kontrol Pemain
Route::get('/play/{game}/{player}', PlayerControls::class)->name('player.controls');

// Route untuk melihat History Permainan
Route::get('/game/{game}/history', function (Game $game) {
    // Ambil data history urut dari yang terlama
    $histories = $game->histories()->with('player')->orderBy('id')->get();
    $players = $game->players;
    
    return view('history', compact('game', 'histories', 'players'));
})->name('game.history');

Route::get('/game/{game}/download-pdf', function (Game $game) {
    // 1. Ambil data history
    $histories = $game->histories()->with('player')->orderBy('id')->get();
    
    // 2. Ambil data pemain & urutkan ranking
    $players = $game->players()->orderByDesc('score')->get();
    
    // 3. Tentukan siapa yang Tanda Tangan
    $champion = $players->first(); // Juara 1
    $loser = $players->last();     // Juara Terakhir (Badut)

    // 4. Generate PDF
    // Kita pakai kertas A4 Portrait
    $pdf = Pdf::loadView('pdf.game-report', compact('game', 'histories', 'players', 'champion', 'loser'));
    
    // 5. Download file dengan nama unik
    return $pdf->download('Berita-Acara-Remi-'.$game->room_code.'.pdf');

})->name('game.download-pdf');

// Route Download Surat Menyerah
Route::get('/game/{game}/surrender-pdf/{player}', function (Game $game, Player $player) {
    
    // Ambil data juara (skor tertinggi saat ini) untuk jadi saksi
    $winner = $game->players()->orderByDesc('score')->first();

    $pdf = Pdf::loadView('pdf.surrender-letter', compact('game', 'player', 'winner'));
    
    // Set kertas A4
    $pdf->setPaper('a4', 'portrait');

    return $pdf->download('SURAT-PERNYATAAN-MENYERAH-'.$player->name.'.pdf');

})->name('game.surrender-pdf');
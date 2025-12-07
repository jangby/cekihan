<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\GameHost;
use App\Livewire\GamePlayer;
use App\Livewire\GameDashboard;
use App\Livewire\PlayerControls;

Route::get('/', GameHost::class)->name('home');

// Placeholder untuk route join pemain (nanti kita buat)
Route::get('/join/{game}/{position}', GamePlayer::class)->name('player.join');

Route::get('/game/{game}', GameDashboard::class)->name('game.dashboard');

// Route untuk Kontrol Pemain
Route::get('/play/{game}/{player}', PlayerControls::class)->name('player.controls');

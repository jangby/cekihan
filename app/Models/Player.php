<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $guarded = ['id'];

    // Relasi: Pemain milik satu Game
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Relasi: Pemain punya banyak Riwayat Skor
    public function histories()
    {
        return $this->hasMany(ScoreHistory::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Penting untuk UUID
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasUuids; // Aktifkan fitur UUID

    protected $guarded = ['id']; // Semua kolom boleh diisi kecuali ID

    // Relasi: Satu Game punya banyak Pemain
    public function players()
    {
        return $this->hasMany(Player::class);
    }

    // Relasi: Satu Game punya banyak Riwayat Skor
    public function histories()
    {
        return $this->hasMany(ScoreHistory::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreHistory extends Model
{
    protected $guarded = ['id'];

    // Relasi ke Player
    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
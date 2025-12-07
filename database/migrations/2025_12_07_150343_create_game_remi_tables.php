<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Games (Sesi Permainan)
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Kita pakai UUID biar URL-nya unik (tidak bisa ditebak)
            $table->string('room_code', 6)->unique(); // Kode ruangan pendek (opsional, buat join manual)
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->integer('current_qr_position')->default(1); // 1-4, Menandakan QR siapa yang sedang tampil
            $table->uuid('winner_id')->nullable(); // ID Pemain yang menang
            $table->timestamps();
        });

        // 2. Tabel Players (Data 4 Pemain)
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('game_id')->constrained('games')->onDelete('cascade');
            $table->string('name');
            $table->integer('position'); // 1, 2, 3, atau 4 (Penting untuk aturan Cekih)
            $table->integer('score')->default(0); // Skor awal 0
            $table->boolean('is_jongkok')->default(false); // Status Jongkok (True/False)
            $table->string('device_token')->nullable(); // Token unik browser HP pemain (biar kalau refresh gak logout)
            $table->timestamps();
        });

        // 3. Tabel Score Histories (Riwayat Transaksi Poin)
        // Ini mencatat setiap perubahan poin: Input biasa, Cekih, atau Kena Reset
        Schema::create('score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->integer('round_number'); // Putaran ke berapa
            $table->integer('points_added'); // Poin yang ditambahkan/dikurang (bisa + atau -)
            
            // Tipe perubahan skor: 
            // 'round' = Input akhir putaran biasa
            // 'cekih_bonus' = Dapat poin karena berhasil mencekih
            // 'cekih_penalty' = Kurang poin karena kena cekih
            // 'reset' = Poin jadi 0 karena disalip (Aturan Tabrak)
            $table->enum('type', ['round', 'cekih_bonus', 'cekih_penalty', 'reset']); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_histories');
        Schema::dropIfExists('players');
        Schema::dropIfExists('games');
    }
};
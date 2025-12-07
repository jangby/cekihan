<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('players', function (Blueprint $table) {
        $table->integer('temp_round_score')->default(0); // Nilai sementara ronde ini
        $table->boolean('has_submitted_round')->default(false); // Status sudah input atau belum
    });
}

public function down(): void
{
    Schema::table('players', function (Blueprint $table) {
        $table->dropColumn(['temp_round_score', 'has_submitted_round']);
    });
}
};

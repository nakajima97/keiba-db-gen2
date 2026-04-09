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
        Schema::create('race_payout_horses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_payout_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('horse_number')->comment('馬番（枠連の場合は枠番）');
            $table->unsignedTinyInteger('sort_order')->comment('順序。馬単・三連単は着順(1着=1)、それ以外は昇順ソート済みの並び順');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_payout_horses');
    }
};

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
        Schema::create('race_nomination_column_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete()->comment('印列（出馬表と共通）。他人列(other)のみメモ作成可（バリデーションで担保）');
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete()->comment('対象の登録馬');
            $table->string('content', 1000)->comment('メモ本文 1000文字以内 プレーンテキスト');
            $table->timestamps();

            // race_mark_memos と同様、印と独立したライフサイクルにする。
            // 印を解除してもメモは残る。
            $table->unique(['race_mark_column_id', 'race_nomination_id'], 'race_nom_col_memos_column_nomination_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_nomination_column_memos');
    }
};

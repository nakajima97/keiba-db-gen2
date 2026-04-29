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
        Schema::create('race_mark_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete()->comment('印列。他人列(other)のみメモ作成可（バリデーションで担保）');
            $table->foreignId('race_entry_id')->constrained()->cascadeOnDelete()->comment('対象の出走馬');
            $table->string('content', 1000)->comment('メモ本文 1000文字以内 プレーンテキスト');
            $table->timestamps();

            // 印 (race_marks) と独立したライフサイクルにするため race_mark_id ではなく
            // (race_mark_column_id, race_entry_id) の組をキーにする。
            // 印を解除（race_marks から行が消える）してもメモは残る。
            $table->unique(['race_mark_column_id', 'race_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_mark_memos');
    }
};

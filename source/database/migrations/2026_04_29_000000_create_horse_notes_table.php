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
        Schema::create('horse_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('horse_id')->constrained()->restrictOnDelete();
            $table->foreignId('race_id')->nullable()->constrained()->restrictOnDelete()->comment('紐づくレース。次走への備忘録など、レースに紐づかないメモは null');
            $table->string('content', 1000)->comment('メモ本文 1000文字以内 プレーンテキスト');
            $table->timestamps();

            // (user_id, horse_id, race_id) の組み合わせでメモは1件のみ。
            // race_id が null の場合（レースに紐づかないメモ）も1ユーザー×1馬で1件のみとする。
            // MySQL の UNIQUE 制約は NULL を重複扱いしないため、アプリケーション層でも重複チェックする想定。
            $table->unique(['user_id', 'horse_id', 'race_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horse_notes');
    }
};

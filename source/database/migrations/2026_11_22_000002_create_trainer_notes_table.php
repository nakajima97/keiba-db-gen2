<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained()->restrictOnDelete();
            $table->string('content', 1000)->comment('メモ本文 1000文字以内 プレーンテキスト（調教師コメントの信用度などの所感）');
            $table->timestamps();

            $table->unique(['user_id', 'trainer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_notes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained()->restrictOnDelete()->comment('発言した調教師');
            $table->enum('sentiment', ['positive', 'conditional', 'negative'])->comment('向き: 前向き / 条件付き / 弱気');
            $table->string('content', 1000)->comment('コメント内容 1000文字以内 プレーンテキスト');
            $table->enum('outcome', ['hit', 'miss', 'undeterminable'])->nullable()->comment('当否: 当たり / 外れ / 判定不能。null は未入力');
            $table->timestamps();

            $table->index(['trainer_id', 'sentiment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_comments');
    }
};

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
        Schema::create('race_nomination_column_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete()->comment('印列（出馬表と共通）。他人列(other)のみ設定可（バリデーションで担保）');
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete()->comment('対象の登録馬');
            $table->enum('mark_value', ['◎', '○', '▲', '△', '☆', '✓']);
            $table->timestamps();

            $table->unique(['race_mark_column_id', 'race_nomination_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_nomination_column_marks');
    }
};

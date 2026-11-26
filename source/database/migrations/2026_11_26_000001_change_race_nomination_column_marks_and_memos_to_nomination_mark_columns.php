<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 登録馬の他人の印・印メモの印列を、出馬表と共通の race_mark_columns から
 * 登録馬専用の race_nomination_mark_columns に切り替える。
 * 本番データ未投入のため、データ移行はせずテーブルを作り直す。
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('race_nomination_column_marks');
        Schema::dropIfExists('race_nomination_column_memos');

        Schema::create('race_nomination_column_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_nomination_mark_column_id')->constrained()->cascadeOnDelete()->comment('登録馬用の他人の印列');
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete()->comment('対象の登録馬');
            $table->enum('mark_value', ['◎', '○', '▲', '△', '☆', '✓']);
            $table->timestamps();

            $table->unique(['race_nomination_mark_column_id', 'race_nomination_id'], 'race_nom_col_marks_column_nomination_unique');
        });

        Schema::create('race_nomination_column_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_nomination_mark_column_id')->constrained()->cascadeOnDelete()->comment('登録馬用の他人の印列');
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete()->comment('対象の登録馬');
            $table->string('content', 1000)->comment('メモ本文 1000文字以内 プレーンテキスト');
            $table->timestamps();

            // 印と独立したライフサイクルにする。印を解除してもメモは残る。
            $table->unique(['race_nomination_mark_column_id', 'race_nomination_id'], 'race_nom_col_memos_column_nomination_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_nomination_column_marks');
        Schema::dropIfExists('race_nomination_column_memos');

        Schema::create('race_nomination_column_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete();
            $table->enum('mark_value', ['◎', '○', '▲', '△', '☆', '✓']);
            $table->timestamps();

            $table->unique(['race_mark_column_id', 'race_nomination_id'], 'race_nom_col_marks_column_nomination_unique');
        });

        Schema::create('race_nomination_column_memos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete();
            $table->string('content', 1000);
            $table->timestamps();

            $table->unique(['race_mark_column_id', 'race_nomination_id'], 'race_nom_col_memos_column_nomination_unique');
        });
    }
};

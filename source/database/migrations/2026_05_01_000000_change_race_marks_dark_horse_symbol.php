<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * issue #206「印の星が分かりにくい」対応。
 *
 * `race_marks.mark_value` の穴馬記号を `×` から `☆` に変更する。
 * `×` は本来「消し」を意味し、穴馬の記号としては一般的でないため、
 * 標準的な穴馬記号 `☆` に置き換える。
 *
 * カラムは ENUM 型のため、新旧両方の値を一時的に許可した上でデータを変換し、
 * 最後に旧値を取り除く 3 ステップで安全に移行する。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_marks', function (Blueprint $table): void {
            $table->enum('mark_value', ['◎', '○', '▲', '△', '×', '☆', '✓'])->change();
        });

        DB::table('race_marks')
            ->where('mark_value', '×')
            ->update(['mark_value' => '☆']);

        Schema::table('race_marks', function (Blueprint $table): void {
            $table->enum('mark_value', ['◎', '○', '▲', '△', '☆', '✓'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('race_marks', function (Blueprint $table): void {
            $table->enum('mark_value', ['◎', '○', '▲', '△', '×', '☆', '✓'])->change();
        });

        DB::table('race_marks')
            ->where('mark_value', '☆')
            ->update(['mark_value' => '×']);

        Schema::table('race_marks', function (Blueprint $table): void {
            $table->enum('mark_value', ['◎', '○', '▲', '△', '×', '✓'])->change();
        });
    }
};

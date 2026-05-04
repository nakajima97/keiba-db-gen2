<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_payouts', function (Blueprint $table) {
            $table->unsignedSmallInteger('popularity')->comment('人気順位')->change();
        });
    }

    public function down(): void
    {
        // unsignedTinyInteger の最大値(255)を超える値があると縮退時に truncate/エラーになるため、先に 255 で丸める。
        DB::table('race_payouts')->where('popularity', '>', 255)->update(['popularity' => 255]);

        Schema::table('race_payouts', function (Blueprint $table) {
            $table->unsignedTinyInteger('popularity')->comment('人気順位')->change();
        });
    }
};

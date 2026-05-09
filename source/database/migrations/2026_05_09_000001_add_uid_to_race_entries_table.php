<?php

use App\Support\NanoId;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('id')->comment('URL用のnanoid');
        });

        // 既存レコードにuidを付与（大規模テーブル対応のため chunkById でメモリ消費を抑える）
        DB::table('race_entries')
            ->whereNull('uid')
            ->orderBy('id')
            ->chunkById(1000, function ($entries) {
                foreach ($entries as $entry) {
                    DB::table('race_entries')->where('id', $entry->id)->update([
                        'uid' => NanoId::generate(),
                    ]);
                }
            });

        Schema::table('race_entries', function (Blueprint $table) {
            $table->string('uid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->string('uid')->nullable()->after('id')->comment('URL用のnanoid');
        });

        // 既存レコードにuidを付与
        $races = DB::table('races')->whereNull('uid')->get();
        foreach ($races as $race) {
            DB::table('races')->where('id', $race->id)->update([
                'uid' => Str::random(21),
            ]);
        }

        Schema::table('races', function (Blueprint $table) {
            $table->string('uid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            $table->dropColumn('uid');
        });
    }
};

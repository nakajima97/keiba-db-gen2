<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horses', function (Blueprint $table) {
            $table->unsignedSmallInteger('birth_year')->nullable()->change();
        });
    }

    public function down(): void
    {
        // firstOrCreate で birth_year なしに作成されたレコードは 0 で補完してから NOT NULL に戻す
        DB::table('horses')->whereNull('birth_year')->update(['birth_year' => 0]);

        Schema::table('horses', function (Blueprint $table) {
            $table->unsignedSmallInteger('birth_year')->nullable(false)->change();
        });
    }
};

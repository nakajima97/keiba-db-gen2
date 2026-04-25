<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_result_horses', function (Blueprint $table) {
            $table->foreignId('horse_id')->nullable()->constrained()->nullOnDelete()->after('race_id');
            $table->foreignId('jockey_id')->nullable()->constrained()->nullOnDelete()->after('horse_id');
        });
    }

    public function down(): void
    {
        Schema::table('race_result_horses', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Horse::class);
            $table->dropForeignIdFor(\App\Models\Jockey::class);
        });
    }
};

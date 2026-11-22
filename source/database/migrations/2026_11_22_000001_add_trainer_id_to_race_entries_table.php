<?php

use App\Models\Trainer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_entries', function (Blueprint $table) {
            $table->foreignId('trainer_id')
                ->nullable()
                ->after('jockey_id')
                ->comment('調教師。既存データおよび調教師未入力の出走馬は null')
                ->constrained()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('race_entries', function (Blueprint $table) {
            $table->dropForeignIdFor(Trainer::class);
        });
    }
};

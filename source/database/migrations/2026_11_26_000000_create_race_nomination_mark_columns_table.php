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
        Schema::create('race_nomination_mark_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 32)->comment('他人の印列のラベル（空文字列可）');
            $table->unsignedSmallInteger('display_order')->comment('表示順 昇順');
            $table->timestamps();

            $table->unique(['race_id', 'user_id', 'display_order'], 'race_nom_mark_columns_race_user_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_nomination_mark_columns');
    }
};

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
        Schema::create('race_mark_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('column_type', ['own', 'other']);
            $table->string('label')->nullable()->comment('他人の印列のラベル（own のときは null）');
            $table->unsignedSmallInteger('display_order')->comment('表示順 昇順');
            $table->timestamps();

            $table->unique(['race_id', 'user_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_mark_columns');
    }
};

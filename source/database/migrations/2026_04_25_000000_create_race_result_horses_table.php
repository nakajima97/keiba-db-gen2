<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_result_horses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('finishing_order')->unsigned();
            $table->tinyInteger('frame_number')->unsigned();
            $table->tinyInteger('horse_number')->unsigned();
            $table->string('horse_name');
            $table->string('sex_age', 10);
            $table->decimal('weight', 4, 1);
            $table->string('jockey_name');
            $table->string('race_time', 20);
            $table->string('time_difference', 30)->nullable();
            $table->string('corner_order', 50)->nullable();
            $table->decimal('estimated_pace', 4, 1)->nullable();
            $table->smallInteger('horse_weight')->nullable();
            $table->smallInteger('horse_weight_change')->nullable();
            $table->string('trainer_name');
            $table->tinyInteger('popularity')->unsigned();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_result_horses');
    }
};

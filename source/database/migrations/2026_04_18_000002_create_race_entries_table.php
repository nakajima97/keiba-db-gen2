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
        Schema::create('race_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained()->restrictOnDelete();
            $table->foreignId('horse_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('frame_number')->comment('枠番 1〜8');
            $table->unsignedTinyInteger('horse_number')->comment('馬番 1〜18');
            $table->foreignId('jockey_id')->constrained()->restrictOnDelete();
            $table->decimal('weight', 4, 1)->comment('負担重量 (kg)');
            $table->unsignedSmallInteger('horse_weight')->nullable()->comment('馬体重 (kg)');
            $table->timestamps();

            $table->unique(['race_id', 'horse_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_entries');
    }
};

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
        Schema::create('race_nomination_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_nomination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['check', 'excluded'])->comment('check: チェック / excluded: 消し。行がなければ未設定');
            $table->timestamps();

            $table->unique(['race_nomination_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_nomination_marks');
    }
};

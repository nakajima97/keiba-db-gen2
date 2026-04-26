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
        Schema::create('race_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_mark_column_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_entry_id')->constrained()->cascadeOnDelete();
            $table->enum('mark_value', ['◎', '○', '▲', '△', '×', '✓']);
            $table->timestamps();

            $table->unique(['race_mark_column_id', 'race_entry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_marks');
    }
};

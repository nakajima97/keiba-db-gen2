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
        Schema::create('buy_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('single/nagashi/box/formation');
            $table->string('label')->comment('通常/流し/ボックス/フォーメーション');
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_types');
    }
};

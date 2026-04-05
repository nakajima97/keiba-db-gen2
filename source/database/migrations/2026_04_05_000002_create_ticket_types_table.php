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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('tansho/fukusho/wakuren/umaren/umatan/wide/sanrenpuku/sanrentan');
            $table->string('label')->comment('単勝/複勝/枠連/馬連/馬単/ワイド/三連複/三連単');
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};

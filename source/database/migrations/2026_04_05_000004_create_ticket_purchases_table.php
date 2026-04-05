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
        Schema::create('ticket_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('buy_type_id')->constrained()->restrictOnDelete();
            $table->json('selections')->comment(
                'box/single: {"horses":[1,3,5]} | '.
                'nagashi: {"axis":[3],"others":[1,5,7]} | '.
                'formation: {"columns":[[1,2],[3,4],[5,6,7]]}'
            );
            $table->unsignedInteger('amount')->nullable()->comment('購入金額（円）');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_purchases');
    }
};

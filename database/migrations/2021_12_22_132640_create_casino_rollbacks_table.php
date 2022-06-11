<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasinoRollbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casino_rollbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('currency')->nullable();
            $table->string('game_uuid')->nullable();
            $table->string('player_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('rollback_transactions')->nullable();
            $table->string('session_id')->nullable();
            $table->string('type')->nullable();
            $table->string('provider_round_id')->nullable();
            $table->string('round_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('casino_rollbacks');
    }
}

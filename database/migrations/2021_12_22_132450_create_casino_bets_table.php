<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasinoBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casino_bets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->double('amount', 16, 8)->default(0);
            $table->string('currency')->nullable();
            $table->string('game_uuid')->nullable();
            $table->string('player_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('type')->nullable()->nullable();
            $table->string('freespin_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('round_id')->nullable();
            $table->boolean('finished')->default(0);
            $table->integer('rollback')->default(0);
            $table->string('status')->nullable();
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
        Schema::dropIfExists('casino_bets');
    }
}

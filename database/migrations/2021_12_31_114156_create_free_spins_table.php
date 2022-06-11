<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreeSpinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('free_spins', function (Blueprint $table) {
            $table->id();
            $table->string('player_id')->nullable();
            $table->string('currency')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('quantity_left')->default(0);
            $table->bigInteger('valid_from')->default(0);
            $table->bigInteger('valid_until')->default(0);
            $table->string('freespin_id')->nullable();
            $table->integer('bet_id')->default(0);
            $table->integer('total_bet_id')->default(0);
            $table->double('denomination', 16, 8)->default(0);
            $table->string('game_uuid')->nullable();
            $table->string('status')->nullable();
            $table->integer('is_canceled')->default(0);
            $table->double('total_win', 16, 8)->default(0);
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
        Schema::dropIfExists('free_spins');
    }
}

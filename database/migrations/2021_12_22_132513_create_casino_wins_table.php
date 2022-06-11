<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasinoWinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casino_wins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bet_id')->default(0);
            $table->double('amount', 16, 8)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('type')->nullable();
            $table->string('freespin_id')->nullable();
            $table->string('session_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('round_id')->nullable();
            $table->boolean('finished')->default(0);
            $table->integer('rollback')->default(0);
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
        Schema::dropIfExists('casino_wins');
    }
}

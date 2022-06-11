<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasinoRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('casino_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('bet_id')->default(0);
            $table->string('action')->nullable();
            $table->double('amount', 16, 8)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('bet_transaction_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('freespin_id')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('round_id')->nullable();
            $table->boolean('finished')->default(0);
            $table->string('rollback')->nullable();
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
        Schema::dropIfExists('casino_refunds');
    }
}

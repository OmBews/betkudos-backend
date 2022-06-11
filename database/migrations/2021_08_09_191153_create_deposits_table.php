<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('crypto_currency_id');
            $table->decimal('amount', 16, 8);
            $table->unsignedBigInteger('wallet_address_id');
            $table->string('txid')->unique();
            $table->string('fee_txid')->nullable();
            $table->string('collect_txid')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->string('error')->nullable();
            $table->integer('confirmations');
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
        Schema::dropIfExists('deposits');
    }
}

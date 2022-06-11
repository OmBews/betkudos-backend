<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoCurrenciesCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_currencies_collection', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crypto_currency_id');
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('txid')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('gas_price')->nullable();
            $table->unsignedBigInteger('deposit_id')->nullable();
            $table->string('error')->nullable();
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
        Schema::dropIfExists('crypto_currencies_collection');
    }
}

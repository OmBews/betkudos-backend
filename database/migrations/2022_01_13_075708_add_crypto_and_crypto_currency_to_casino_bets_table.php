<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCryptoAndCryptoCurrencyToCasinoBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casino_bets', function (Blueprint $table) {
            $table->string('crypto_currency')->nullable();
            $table->double('crypto_amt', 16, 10)->default(0);
            $table->double('euro_amt', 16, 10)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('casino_bets', function (Blueprint $table) {
            $table->dropColumn('crypto_currency');
            $table->dropColumn('crypto_amt');   
            $table->dropColumn('euro_amt');    
        });
    }
}

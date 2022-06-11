<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBetLimitsColumnsToCryptoCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_currencies', function (Blueprint $table) {
            $table->decimal('max_bet', 16, 8)->default(0);
            $table->decimal('min_bet', 16, 8)->default(0);
            $table->decimal('max_bet_profit', 16, 8)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_currencies', function (Blueprint $table) {
            //
        });
    }
}

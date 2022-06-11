<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyAndGameUuidToCasinoWinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('casino_wins', function (Blueprint $table) {
            $table->string('currency')->nullable();
            $table->string('game_uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('casino_wins', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->dropColumn('game_uuid');
        });
    }
}

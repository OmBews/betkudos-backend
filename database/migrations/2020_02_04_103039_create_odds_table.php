<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOddsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('odds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('market_id');
            $table->bigInteger('match_id');
            $table->string('bet365_id');
            $table->unsignedBigInteger('match_market_id')->index();
            $table->string('odds');
            $table->string('name')->nullable();
            $table->string('header')->nullable();
            $table->string('handicap')->nullable();
            $table->timestamps();

            $table->unique(['market_id', 'match_id', 'bet365_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('odds');
    }
}

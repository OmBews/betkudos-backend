<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bet365_id');
            $table->bigInteger('bets_api_id')->nullable();
            $table->bigInteger('sport_id')->index();
            $table->bigInteger('home_team_id');
            $table->bigInteger('away_team_id');
            $table->bigInteger('league_id');
            $table->string('cc')->nullable();
            $table->integer('starts_at');
            $table->tinyInteger('time_status');
            $table->integer('last_bets_api_update');
            $table->timestamps();

            $table->unique(['bet365_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('matches');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Leagues\League;

class CreateLeaguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('leagues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bet365_id')->unique()->index();
            $table->bigInteger('bets_api_id')->nullable();
            $table->bigInteger('sport_id');
            $table->unsignedBigInteger('sport_category_id')->nullable();
            $table->string('cc')->nullable();
            $table->string('name')->unique();
            $table->boolean('popular')->default(false);
            $table->boolean('active')->default(true);
            $table->tinyInteger('time_frame')->default(League::DEFAULT_UPDATE_TIME_FRAME);
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
        Schema::connection(config('database.feed_connection'))->dropIfExists('leagues');
    }
}

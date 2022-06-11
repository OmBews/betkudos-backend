<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('teams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bet365_id');
            $table->bigInteger('bets_api_id')->nullable();
            $table->string('name');
            $table->bigInteger('image_id')->nullable();
            $table->string('cc')->nullable();
            $table->timestamps();

            $table->unique(['name', 'bet365_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('teams');
    }
}

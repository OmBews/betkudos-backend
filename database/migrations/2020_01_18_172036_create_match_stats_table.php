<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('match_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('match_id');
            $table->text('stats');
            $table->text('events')->nullable();
            $table->timestamps();

            $table->unique(['match_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('match_stats');
    }
}

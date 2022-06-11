<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('match_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('match_id')->unique();
            $table->bigInteger('bet365_match_id')->unique();
            $table->string('single_score')->nullable();
            $table->text('scores');
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
        Schema::connection(config('database.feed_connection'))->dropIfExists('match_results');
    }
}

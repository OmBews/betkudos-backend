<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPointsToMatchResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->table('match_results', function (Blueprint $table) {
            $table->string('points')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->table('match_results', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
}

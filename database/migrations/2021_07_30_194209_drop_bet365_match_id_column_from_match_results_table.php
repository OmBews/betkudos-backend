<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropBet365MatchIdColumnFromMatchResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->table('match_results', function (Blueprint $table) {
            $table->dropColumn('bet365_match_id');
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
            $table->bigInteger('bet365_match_id')->unique();
        });
    }
}

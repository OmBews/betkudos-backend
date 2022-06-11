<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeDetailsToMatchResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->table('match_results', function (Blueprint $table) {
            $table->boolean('is_playing')->default(false);
            $table->bigInteger('kick_of_time')->default(0);
            $table->bigInteger('passed_minutes')->default(0);
            $table->bigInteger('passed_seconds')->default(0);
            $table->string('current_time')->default(0);
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
            $table->dropColumn('is_playing');
            $table->dropColumn('kick_of_time');
            $table->dropColumn('passed_minutes');
            $table->dropColumn('passed_seconds');
            $table->dropColumn('current_time');
        });
    }
}

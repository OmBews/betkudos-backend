<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnLiveBettingCollumnToSportsTable extends Migration
{
    use \App\Concerns\MemSQL\UsesMemSQLConnection;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->table('sports', function (Blueprint $table) {
            $table->boolean('on_live_betting')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->table('sports', function (Blueprint $table) {
            $table->dropColumn('on_live_betting');
        });
    }

    public function getConnection()
    {
        return $this->getConnectionName();
    }
}

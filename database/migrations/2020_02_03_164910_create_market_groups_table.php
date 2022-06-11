<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('market_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['key', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('market_groups');
    }
}

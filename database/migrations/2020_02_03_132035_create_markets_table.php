<?php

use App\Models\Markets\Market;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('markets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sport_id');
            $table->string('market_groups');
            $table->string('name');
            $table->string('key');
            $table->tinyInteger('priority')->default(0);
            $table->boolean('featured')->default(0);
            $table->text('headers')->nullable();
            $table->string('featured_header')->nullable();
            $table->tinyInteger('layout')->default(Market::DEFAULT_LAYOUT);
            $table->boolean('active')->default(false);
            $table->boolean('popular')->default(false);
            $table->boolean('on_live_betting')->default(false);
            $table->timestamps();

            $table->unique(['sport_id', 'name', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection(config('database.feed_connection'))->dropIfExists('markets');
    }
}

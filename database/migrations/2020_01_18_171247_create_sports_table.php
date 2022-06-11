<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sports\Sport;

class CreateSportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection(config('database.feed_connection'))->create('sports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bet365_id');
            $table->string('name')->unique();
            $table->boolean('active')->default(false);
            $table->tinyInteger('time_frame')->default(Sport::DEFAULT_UPDATE_TIME_FRAME);
            $table->tinyInteger('upcoming_preview_limit')->default(Sport::DEFAULT_PREVIEW_LIMIT);
            $table->tinyInteger('live_preview_limit')->default(Sport::DEFAULT_PREVIEW_LIMIT);
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
        Schema::connection(config('database.feed_connection'))->dropIfExists('sports');
    }
}

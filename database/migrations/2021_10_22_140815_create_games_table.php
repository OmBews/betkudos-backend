<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('aggregator_uuid')->index();
            $table->string('name');
            $table->string('image');
            $table->string('type')->index();
            $table->string('technology')->index();
            $table->boolean('has_lobby')->index();
            $table->boolean('has_jackpot')->default(0);
            $table->decimal('jackpot', 10)->default(0);
            $table->boolean('is_mobile');
            $table->boolean('freespin_valid_until_full_day');
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique('aggregator_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}

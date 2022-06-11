<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockTrackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('block_track', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crypto_currency_id');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->string('cron_hash')->nullable();
            $table->string('error')->nullable();
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
        Schema::dropIfExists('block_track');
    }
}

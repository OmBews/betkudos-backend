<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserKycsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_kycs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->default(0);
            $table->integer('level')->default(0);
            $table->integer('kyc_status')->default(0);
            $table->integer('kyc_status_two')->default(0);
            $table->integer('kyc_status_three')->default(0);
            $table->string('address')->nullable();
            $table->string('fname')->nullable();
            $table->string('lname')->nullable();
            $table->string('city')->nullable();
            $table->string('pin')->nullable();
            $table->integer('date')->default(0);
            $table->integer('month')->default(0);
            $table->integer('year')->default(0);
            $table->string('county')->nullable();
            $table->string('country')->nullable();
            $table->string('notes')->nullable();
            $table->text('level_info')->nullable();
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
        Schema::dropIfExists('user_kycs');
    }
}

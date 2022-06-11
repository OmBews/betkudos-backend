<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bets\Bet;

class CreateBetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->unsignedBigInteger('wallet_id')->nullable();
            $table->string('code', 15)->unique();
            $table->enum('type', Bet::TYPES);
            $table->decimal('stake', 16, 8);
            $table->decimal('profit', 16, 8);
            $table->boolean('live');
            $table->string('status')->default(Bet::STATUS_OPEN);
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
        Schema::dropIfExists('bets');
    }
}

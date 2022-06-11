<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bets\Selections\BetSelection;

class CreateBetSelectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bet_selections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('bet_id');
            $table->bigInteger('match_id');
            $table->bigInteger('market_id');
            $table->bigInteger('odd_id');
            $table->decimal('odds', 6, 2);
            $table->string('status')->default(BetSelection::STATUS_OPEN);
            $table->string('name')->nullable();
            $table->string('header')->nullable();
            $table->string('handicap')->nullable();
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
        Schema::dropIfExists('bet_selections');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sessions\Logs\SessionLog;

class CreateSessionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('session_id');
            $table->ipAddress('ip_address');
            $table->enum('action', [
                SessionLog::ACTION_LOGIN,
                SessionLog::ACTION_LOGOUT,
                SessionLog::ACTION_PASSWORD_UPDATED,
                SessionLog::ACTION_EMAIL_UPDATED,
                SessionLog::ACTION_2FA_ENABLED,
                SessionLog::ACTION_2FA_DISABLED
            ]);
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
        Schema::dropIfExists('session_logs');
    }
}

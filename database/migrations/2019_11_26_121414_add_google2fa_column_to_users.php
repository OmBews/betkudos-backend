<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogle2faColumnToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google2fa_secret')
                  ->nullable()
                  ->after('password');
            $table->boolean('google2fa_enabled')
                  ->default(0)
                  ->after('google2fa_secret');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('google2fa_enabled');
        });
    }
}

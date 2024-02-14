<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersdevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID')->length(11);
            $table->string('deviceID',500)->nullable();
            $table->tinyInteger('deviceType')->default(0)
                    ->comment('0=>iOS, 1=>Android');
            $table->dateTime('loginTime')->nullable();
            $table->dateTime('logoutTime')->nullable();
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
        //
    }
}

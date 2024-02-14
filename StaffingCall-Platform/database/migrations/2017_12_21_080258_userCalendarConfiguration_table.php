<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserCalendarConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_usercalendarsettings', function (Blueprint $table) {
            $table->increments('id');
        $table->integer('userID')->length(11);
        $table->date('onDate')->nullable();
        $table->integer('shiftID')->length(11);
        $table->tinyInteger('availabilityStatus')->default(1)
                    ->comment('0=>Unavailable, 1=>Available');
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

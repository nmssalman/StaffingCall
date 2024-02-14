<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShiftsetupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_shiftsetup', function (Blueprint $table) {
            $table->increments('id');
            $table->string('shiftTitle',100)->nullable();
            $table->integer('businessGroupID')->length(11)->default(0);
            $table->integer('businessUnitID')->length(11)->default(0);
            $table->time('startTime');
            $table->time('endTime');
            $table->tinyInteger('shiftType')->default(0)->comment('0=>Day Shift, 1=>Night Shift');
            $table->tinyInteger('status')->default(1);
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

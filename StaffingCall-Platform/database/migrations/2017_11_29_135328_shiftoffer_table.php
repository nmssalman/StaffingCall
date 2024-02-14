<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShiftofferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_shiftoffer', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('requestID')->length(11);
            $table->integer('userID')->length(11);
            $table->tinyInteger('responseType')->default(0)
                    ->comment('0=>Full Shift, 1=>Partial Shift, 2=>Decline');
            $table->time('shiftTime');
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

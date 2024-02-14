<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShiftconfirmationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_shiftconfirmation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shiftOfferID')->length(11);
            $table->tinyInteger('offerResponse')->default(0)
                    ->comment('0=>Offer Sent, 1=>Offer Accepted By User, 2=>Offer Declined By User');
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

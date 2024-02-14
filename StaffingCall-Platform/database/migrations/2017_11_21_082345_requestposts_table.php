<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RequestpostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_requestposts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('businessGroupID')->length(11)->default(0);
            $table->integer('businessUnitID')->length(11)->default(0);
            $table->integer('requestReasonID')->length(11)->default(0);
            $table->integer('lastMinuteStaffID')->length(11)->default(0);
            $table->time('timeOfCallMade')->nullable();
            $table->integer('vacancyReasonID')->length(11)->default(0);
            $table->integer('requiredStaffCategoryID')->length(11)->default(0);
            $table->integer('numberOfOffers')->length(11)->default(1);
            $table->dateTime('staffingStartDate');
            $table->dateTime('staffingEndDate');
            $table->integer('staffingShiftID')->length(11)->default(0);
            $table->tinyInteger('shiftType')->default(0)->comment('0=>Pre-Shift,1=>Custom-Shift');
            $table->time('customShiftStartTime')->nullable();
            $table->time('customShiftEndTime')->nullable();
            $table->longText('notes')->nullable();
            $table->integer('ownerID')->length(11)->default(0);
            $table->integer('updatedBy')->length(11)->default(0);
            $table->integer('approvedBy')->length(11)->default(0);
            $table->tinyInteger('postingStatus')->default(0)
                  ->comment('0=>Pending,'
                          . '1=>Open-Awaiting,'
                          . '2=>Open-Offer in progress,'
                          . '3=>Close (Completed) ,'
                          . '4=>Close (Cancelled)'
                          );
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

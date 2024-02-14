<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffing_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID')->length(11);
            $table->integer('requestID')->length(11);
            $table->longText('message',500)->nullable();
            $table->tinyInteger('notificationType')->default(0);
            $table->tinyInteger('readStatus')->default(0)
                    ->comment('0=>No, 1=>Yes');
            $table->tinyInteger('deleteStatus')->default(0)
                    ->comment('0=>No, 1=>Yes');
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
